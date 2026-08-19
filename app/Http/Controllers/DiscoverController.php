<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Church;
use App\Models\Genre;
use App\Models\HymnBook;
use App\Models\Language;
use App\Models\MusicGroup;
use App\Models\Occasion;
use App\Models\Poem;
use App\Models\Song;
use App\Support\SongPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscoverController extends Controller
{
    public function index(): Response
    {
        $songQuery = Song::published()->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name']);

        return Inertia::render('Discover/Index', [
            'hero' => [
                'title' => 'Discover Adventist Music',
                'subtitle' => 'Browse songs, albums, artists, choirs and churches from across Malawi.',
            ],
            'chips' => Category::query()->where('is_active', true)->orderBy('sort_order')->limit(12)
                ->get(['id', 'name', 'slug', 'icon', 'color'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon, 'color' => $c->color]),
            'newReleases' => (clone $songQuery)->orderByDesc('published_at')->limit(8)->get()
                ->map(fn (Song $s) => SongPayload::from($s)),
            'topSongs' => (clone $songQuery)->orderByDesc('stream_count')->limit(10)->get()
                ->map(fn (Song $s, int $idx) => SongPayload::from($s, $idx + 1)),
            'trending' => (clone $songQuery)->orderByDesc('like_count')->limit(8)->get()
                ->map(fn (Song $s, int $idx) => SongPayload::from($s, $idx + 1)),
            'albums' => Album::query()->where('is_published', true)
                ->with(['artist:id,name,slug', 'musicGroup:id,name,slug', 'church:id,name'])
                ->orderByDesc('is_featured')->orderByDesc('released_at')->limit(8)->get()
                ->map(fn (Album $a) => $this->albumCard($a)),
            'featuredGroups' => MusicGroup::query()->where('is_active', true)
                ->orderByDesc('is_featured')->orderByDesc('is_verified')->limit(8)->get(['id', 'name', 'slug', 'type', 'image_path'])
                ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'slug' => $g->slug, 'type' => $g->type, 'image' => $g->image_path]),
            'featuredArtists' => Artist::query()->where('is_active', true)
                ->orderByDesc('is_featured')->orderByDesc('is_verified')->limit(8)->get(['id', 'name', 'stage_name', 'slug', 'image_path'])
                ->map(fn ($a) => ['id' => $a->id, 'name' => $a->stage_name ?: $a->name, 'slug' => $a->slug, 'image' => $a->image_path]),
            'occasions' => Occasion::query()->where('is_active', true)->orderBy('sort_order')->limit(8)
                ->get(['id', 'name', 'slug', 'image_path'])
                ->map(fn ($o) => ['id' => $o->id, 'name' => $o->name, 'slug' => $o->slug, 'image' => $o->image_path]),
            'poems' => Poem::published()->with(['artist:id,name,stage_name,slug', 'church:id,name'])
                ->orderByDesc('is_featured')->orderByDesc('published_at')->limit(6)->get()
                ->map(fn (Poem $p) => $this->poemCard($p)),
        ]);
    }

    public function poems(Request $request): Response
    {
        $q = Poem::published()->with(['artist:id,name,stage_name,slug', 'church:id,name', 'category:id,name,slug', 'language:id,name']);

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where('title', 'like', '%' . $search . '%');
        }
        if ($category = $request->string('category')->trim()->toString()) {
            $q->whereHas('category', fn ($sq) => $sq->where('slug', $category));
        }
        if ($language = $request->string('language')->trim()->toString()) {
            $q->whereHas('language', fn ($sq) => $sq->where('slug', $language));
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'popular' => $q->orderByDesc('view_count'),
            'featured' => $q->orderByDesc('is_featured')->orderByDesc('published_at'),
            default => $q->orderByDesc('published_at'),
        };

        $poems = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Poems', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'language' => $request->string('language')->toString(),
                'sort' => $sort,
            ],
            'facets' => [
                'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
                'languages' => Language::query()->orderBy('name')->get(['id', 'name', 'slug']),
            ],
            'poems' => [
                'data' => collect($poems->items())->map(fn (Poem $p) => $this->poemCard($p))->all(),
                'meta' => [
                    'current_page' => $poems->currentPage(),
                    'last_page' => $poems->lastPage(),
                    'total' => $poems->total(),
                ],
            ],
        ]);
    }

    public function songs(Request $request): Response
    {
        $q = Song::published()->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name']);

        if ($genre = $request->string('genre')->trim()->toString()) {
            $q->whereHas('genre', fn ($sq) => $sq->where('slug', $genre));
        }
        if ($language = $request->string('language')->trim()->toString()) {
            $q->whereHas('language', fn ($sq) => $sq->where('slug', $language));
        }
        if ($category = $request->string('category')->trim()->toString()) {
            $q->whereHas('categories', fn ($sq) => $sq->where('slug', $category));
        }
        if ($occasion = $request->string('occasion')->trim()->toString()) {
            $q->whereHas('occasions', fn ($sq) => $sq->where('slug', $occasion));
        }
        if ($search = $request->string('q')->trim()->toString()) {
            $q->where('title', 'like', '%' . $search . '%');
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'popular' => $q->orderByDesc('stream_count'),
            'oldest' => $q->orderBy('published_at'),
            default => $q->orderByDesc('published_at'),
        };

        $songs = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Songs', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'genre' => $request->string('genre')->toString(),
                'language' => $request->string('language')->toString(),
                'category' => $request->string('category')->toString(),
                'occasion' => $request->string('occasion')->toString(),
                'sort' => $sort,
            ],
            'facets' => [
                'genres' => Genre::query()->orderBy('name')->get(['id', 'name', 'slug']),
                'languages' => Language::query()->orderBy('name')->get(['id', 'name', 'slug']),
                'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
                'occasions' => Occasion::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
            ],
            'songs' => [
                'data' => collect($songs->items())->map(fn (Song $s) => SongPayload::from($s))->all(),
                'links' => $songs->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $songs->currentPage(),
                    'last_page' => $songs->lastPage(),
                    'total' => $songs->total(),
                    'per_page' => $songs->perPage(),
                ],
            ],
        ]);
    }

    public function albums(Request $request): Response
    {
        $q = Album::query()->where('is_published', true)
            ->with(['artist:id,name,slug', 'musicGroup:id,name,slug', 'church:id,name'])
            ->withCount('songs');

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where('title', 'like', '%' . $search . '%');
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'title' => $q->orderBy('title'),
            'featured' => $q->orderByDesc('is_featured')->orderByDesc('released_at'),
            default => $q->orderByDesc('released_at'),
        };

        $albums = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Albums', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'sort' => $sort,
            ],
            'albums' => [
                'data' => collect($albums->items())->map(fn (Album $a) => $this->albumCard($a))->all(),
                'meta' => [
                    'current_page' => $albums->currentPage(),
                    'last_page' => $albums->lastPage(),
                    'total' => $albums->total(),
                    'per_page' => $albums->perPage(),
                ],
            ],
        ]);
    }

    public function artists(Request $request): Response
    {
        $q = Artist::query()->where('is_active', true)
            ->withCount(['songs as songs_count' => fn ($sq) => $sq->where('status', Song::STATUS_PUBLISHED)]);

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where(function ($sq) use ($search) {
                $sq->where('name', 'like', '%' . $search . '%')
                    ->orWhere('stage_name', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->string('sort', 'featured')->toString();
        match ($sort) {
            'name' => $q->orderBy('name'),
            'songs' => $q->orderByDesc('songs_count'),
            default => $q->orderByDesc('is_featured')->orderByDesc('is_verified')->orderBy('name'),
        };

        $artists = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Artists', [
            'filters' => ['q' => $request->string('q')->toString(), 'sort' => $sort],
            'artists' => [
                'data' => collect($artists->items())->map(fn (Artist $a) => [
                    'id' => $a->id,
                    'name' => $a->stage_name ?: $a->name,
                    'slug' => $a->slug,
                    'image' => $a->image_path,
                    'is_verified' => $a->is_verified,
                    'songs_count' => $a->songs_count,
                ])->all(),
                'meta' => [
                    'current_page' => $artists->currentPage(),
                    'last_page' => $artists->lastPage(),
                    'total' => $artists->total(),
                ],
            ],
        ]);
    }

    public function groups(Request $request): Response
    {
        $q = MusicGroup::query()->where('is_active', true)
            ->with(['church:id,name'])
            ->withCount(['songs as songs_count' => fn ($sq) => $sq->where('status', Song::STATUS_PUBLISHED)]);

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where('name', 'like', '%' . $search . '%');
        }
        if ($type = $request->string('type')->trim()->toString()) {
            $q->where('type', $type);
        }

        $sort = $request->string('sort', 'featured')->toString();
        match ($sort) {
            'name' => $q->orderBy('name'),
            'songs' => $q->orderByDesc('songs_count'),
            default => $q->orderByDesc('is_featured')->orderByDesc('is_verified')->orderBy('name'),
        };

        $groups = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Groups', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'type' => $request->string('type')->toString(),
                'sort' => $sort,
            ],
            'types' => [
                ['value' => 'choir', 'label' => 'Choir'],
                ['value' => 'quartet', 'label' => 'Quartet'],
                ['value' => 'acapella', 'label' => 'Acapella'],
                ['value' => 'band', 'label' => 'Band'],
                ['value' => 'family', 'label' => 'Family'],
                ['value' => 'youth', 'label' => 'Youth'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'groups' => [
                'data' => collect($groups->items())->map(fn (MusicGroup $g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'slug' => $g->slug,
                    'type' => $g->type,
                    'image' => $g->image_path,
                    'is_verified' => $g->is_verified,
                    'church' => $g->church?->name,
                    'songs_count' => $g->songs_count,
                ])->all(),
                'meta' => [
                    'current_page' => $groups->currentPage(),
                    'last_page' => $groups->lastPage(),
                    'total' => $groups->total(),
                ],
            ],
        ]);
    }

    public function churches(Request $request): Response
    {
        $q = Church::query()->where('is_active', true)
            ->with(['region:id,name', 'district:id,name'])
            ->withCount(['musicGroups as groups_count' => fn ($sq) => $sq->where('is_active', true)]);

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where('name', 'like', '%' . $search . '%');
        }

        $q->orderByDesc('is_featured')->orderByDesc('is_verified')->orderBy('name');

        $churches = $q->paginate(24)->withQueryString();

        return Inertia::render('Discover/Churches', [
            'filters' => ['q' => $request->string('q')->toString()],
            'churches' => [
                'data' => collect($churches->items())->map(fn (Church $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image' => $c->image_path,
                    'is_verified' => $c->is_verified,
                    'region' => $c->region?->name,
                    'district' => $c->district?->name,
                    'groups_count' => $c->groups_count,
                ])->all(),
                'meta' => [
                    'current_page' => $churches->currentPage(),
                    'last_page' => $churches->lastPage(),
                    'total' => $churches->total(),
                ],
            ],
        ]);
    }

    public function trending(): Response
    {
        $items = Song::published()
            ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
            ->withCount(['streams as recent_streams' => fn ($q) => $q->where('created_at', '>=', now()->subDays(7))->where('counted', true)])
            ->orderByDesc('recent_streams')
            ->orderByDesc('stream_count')
            ->limit(50)->get();

        return Inertia::render('Discover/Trending', [
            'items' => $items->map(fn (Song $s, int $idx) => SongPayload::from($s, $idx + 1)),
        ]);
    }

    public function top100(): Response
    {
        $items = Song::published()
            ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
            ->orderByDesc('stream_count')
            ->limit(100)->get();

        return Inertia::render('Discover/Top100', [
            'items' => $items->map(fn (Song $s, int $idx) => SongPayload::from($s, $idx + 1)),
        ]);
    }

    public function occasions(): Response
    {
        $occasions = Occasion::query()->where('is_active', true)->orderBy('sort_order')
            ->withCount(['songs as songs_count' => fn ($q) => $q->where('status', Song::STATUS_PUBLISHED)])
            ->get(['id', 'name', 'slug', 'image_path', 'description']);

        return Inertia::render('Discover/Occasions', [
            'occasions' => $occasions->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'slug' => $o->slug,
                'description' => $o->description,
                'image' => $o->image_path,
                'songs_count' => $o->songs_count,
            ]),
        ]);
    }

    public function occasion(Occasion $occasion): Response
    {
        abort_unless($occasion->is_active, 404);

        $songs = $occasion->songs()->where('status', Song::STATUS_PUBLISHED)
            ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
            ->orderByDesc('published_at')
            ->paginate(24)->withQueryString();

        return Inertia::render('Discover/OccasionShow', [
            'occasion' => [
                'id' => $occasion->id,
                'name' => $occasion->name,
                'slug' => $occasion->slug,
                'description' => $occasion->description,
                'image' => $occasion->image_path,
            ],
            'songs' => [
                'data' => collect($songs->items())->map(fn (Song $s) => SongPayload::from($s))->all(),
                'meta' => [
                    'current_page' => $songs->currentPage(),
                    'last_page' => $songs->lastPage(),
                    'total' => $songs->total(),
                ],
            ],
        ]);
    }

    public function hymnBooks(): Response
    {
        $books = HymnBook::query()->where('is_active', true)
            ->with('language:id,name')
            ->withCount('hymns')
            ->orderBy('name')->get();

        return Inertia::render('Discover/HymnBooks', [
            'books' => $books->map(fn (HymnBook $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'language' => $b->language?->name,
                'publisher' => $b->publisher,
                'published_year' => $b->published_year,
                'cover' => $b->cover_path,
                'hymns_count' => $b->hymns_count,
            ]),
        ]);
    }

    public function search(Request $request): Response
    {
        $query = $request->string('q')->trim()->toString();

        if ($query === '' || strlen($query) < 2) {
            return Inertia::render('Discover/Search', [
                'query' => $query,
                'results' => ['songs' => [], 'albums' => [], 'artists' => [], 'groups' => [], 'churches' => [], 'poems' => []],
            ]);
        }

        $like = '%' . $query . '%';

        $songs = Song::published()->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
            ->where('title', 'like', $like)->orderByDesc('stream_count')->limit(20)->get();
        $albums = Album::query()->where('is_published', true)
            ->with(['artist:id,name,slug', 'musicGroup:id,name,slug'])
            ->where('title', 'like', $like)->limit(12)->get();
        $artists = Artist::query()->where('is_active', true)
            ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('stage_name', 'like', $like))
            ->limit(12)->get();
        $groups = MusicGroup::query()->where('is_active', true)
            ->where('name', 'like', $like)->limit(12)->get();
        $churches = Church::query()->where('is_active', true)
            ->where('name', 'like', $like)->limit(12)->get();
        $poems = Poem::published()->with(['artist:id,name,stage_name,slug', 'church:id,name', 'category:id,name'])
            ->where('title', 'like', $like)->orderByDesc('view_count')->limit(12)->get();

        return Inertia::render('Discover/Search', [
            'query' => $query,
            'results' => [
                'songs' => $songs->map(fn (Song $s) => SongPayload::from($s)),
                'albums' => $albums->map(fn (Album $a) => $this->albumCard($a)),
                'artists' => $artists->map(fn (Artist $a) => [
                    'id' => $a->id,
                    'name' => $a->stage_name ?: $a->name,
                    'slug' => $a->slug,
                    'image' => $a->image_path,
                    'is_verified' => $a->is_verified,
                ]),
                'groups' => $groups->map(fn (MusicGroup $g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'slug' => $g->slug,
                    'type' => $g->type,
                    'image' => $g->image_path,
                ]),
                'churches' => $churches->map(fn (Church $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image' => $c->image_path,
                ]),
                'poems' => $poems->map(fn (Poem $p) => $this->poemCard($p)),
            ],
        ]);
    }

    private function albumCard(Album $a): array
    {
        return [
            'id' => $a->id,
            'title' => $a->title,
            'slug' => $a->slug,
            'artwork' => $a->artwork_path,
            'artist' => $a->musicGroup?->name
                ?? ($a->artist?->stage_name ?: $a->artist?->name)
                ?? $a->church?->name
                ?? 'Various',
            'year' => $a->release_year,
            'songs_count' => $a->songs_count ?? null,
        ];
    }

    private function poemCard(Poem $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'summary' => $p->summary,
            'image' => $p->image_path,
            'author' => $p->displayAuthor(),
            'category' => $p->category?->name,
            'language' => $p->language?->name,
            'published_at' => optional($p->published_at)->toDateString(),
        ];
    }
}
