<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Church;
use App\Models\MusicGroup;
use App\Models\Poem;
use App\Models\Song;
use App\Support\MediaUrl;
use App\Support\SongPayload;
use Inertia\Inertia;
use Inertia\Response;

class ProfilesController extends Controller
{
    public function song(Song $song): Response
    {
        abort_unless($song->status === Song::STATUS_PUBLISHED, 404);

        $song->load([
            'musicGroup:id,name,slug,image_path',
            'artist:id,name,stage_name,slug,image_path',
            'church:id,name,slug',
            'album:id,title,slug,artwork_path',
            'language:id,name',
            'genre:id,name',
            'categories:id,name,slug',
            'occasions:id,name,slug',
            'featuredArtists:id,name,stage_name,slug',
        ]);

        $related = Song::published()
            ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
            ->when($song->music_group_id, fn ($q) => $q->where('music_group_id', $song->music_group_id))
            ->when(! $song->music_group_id && $song->artist_id, fn ($q) => $q->where('artist_id', $song->artist_id))
            ->when(! $song->music_group_id && ! $song->artist_id && $song->church_id, fn ($q) => $q->where('church_id', $song->church_id))
            ->where('id', '!=', $song->id)
            ->orderByDesc('stream_count')
            ->limit(8)->get();

        return Inertia::render('Profiles/Song', [
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'slug' => $song->slug,
                'description' => $song->description,
                'lyrics' => $song->lyrics,
                'artwork' => MediaUrl::url($song->artwork_path),
                'audio' => MediaUrl::url($song->audio_path),
                'duration' => $song->duration_seconds,
                'streams' => $song->stream_count,
                'likes' => $song->like_count,
                'downloads' => $song->download_count,
                'allow_download' => (bool) $song->allow_download,
                'is_featured' => (bool) $song->is_featured,
                'released_at' => optional($song->released_at)->toDateString(),
                'release_year' => $song->release_year,
                'group' => $song->musicGroup ? [
                    'name' => $song->musicGroup->name,
                    'slug' => $song->musicGroup->slug,
                    'image' => MediaUrl::url($song->musicGroup->image_path),
                ] : null,
                'artist' => $song->artist ? [
                    'name' => $song->artist->stage_name ?: $song->artist->name,
                    'slug' => $song->artist->slug,
                    'image' => MediaUrl::url($song->artist->image_path),
                ] : null,
                'church' => $song->church ? ['name' => $song->church->name, 'slug' => $song->church->slug] : null,
                'album' => $song->album ? [
                    'title' => $song->album->title,
                    'slug' => $song->album->slug,
                    'artwork' => MediaUrl::url($song->album->artwork_path),
                ] : null,
                'language' => $song->language?->name,
                'genre' => $song->genre?->name,
                'categories' => $song->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug]),
                'occasions' => $song->occasions->map(fn ($o) => ['name' => $o->name, 'slug' => $o->slug]),
                'featured_artists' => $song->featuredArtists->map(fn ($a) => [
                    'name' => $a->stage_name ?: $a->name,
                    'slug' => $a->slug,
                ]),
            ],
            'payload' => SongPayload::from($song),
            'related' => $related->map(fn (Song $s) => SongPayload::from($s)),
        ]);
    }

    public function album(Album $album): Response
    {
        abort_unless($album->is_published, 404);

        $album->load([
            'artist:id,name,stage_name,slug,image_path',
            'musicGroup:id,name,slug,image_path',
            'church:id,name,slug',
            'primaryLanguage:id,name',
            'songs' => fn ($q) => $q->where('status', Song::STATUS_PUBLISHED),
            'songs.musicGroup:id,name,slug',
            'songs.artist:id,name,slug',
            'songs.church:id,name',
        ]);

        return Inertia::render('Profiles/Album', [
            'album' => [
                'id' => $album->id,
                'title' => $album->title,
                'slug' => $album->slug,
                'description' => $album->description,
                'artwork' => MediaUrl::url($album->artwork_path),
                'release_year' => $album->release_year,
                'released_at' => optional($album->released_at)->toDateString(),
                'label' => $album->label,
                'language' => $album->primaryLanguage?->name,
                'is_featured' => (bool) $album->is_featured,
                'artist' => $album->artist ? [
                    'name' => $album->artist->stage_name ?: $album->artist->name,
                    'slug' => $album->artist->slug,
                    'image' => MediaUrl::url($album->artist->image_path),
                ] : null,
                'group' => $album->musicGroup ? [
                    'name' => $album->musicGroup->name,
                    'slug' => $album->musicGroup->slug,
                    'image' => MediaUrl::url($album->musicGroup->image_path),
                ] : null,
                'church' => $album->church ? [
                    'name' => $album->church->name,
                    'slug' => $album->church->slug,
                ] : null,
            ],
            'songs' => $album->songs->map(fn (Song $s, int $idx) => SongPayload::from($s, $s->track_number ?: $idx + 1)),
        ]);
    }

    public function artist(Artist $artist): Response
    {
        abort_unless($artist->is_active, 404);

        $artist->load([
            'church:id,name,slug',
            'region:id,name',
            'district:id,name',
        ]);

        $songs = $artist->songs()->where('status', Song::STATUS_PUBLISHED)
            ->with(['musicGroup:id,name,slug', 'church:id,name'])
            ->orderByDesc('stream_count')
            ->limit(50)->get();

        $albums = $artist->albums()->where('is_published', true)
            ->orderByDesc('released_at')->limit(12)->get();

        return Inertia::render('Profiles/Artist', [
            'artist' => [
                'id' => $artist->id,
                'name' => $artist->stage_name ?: $artist->name,
                'real_name' => $artist->name,
                'slug' => $artist->slug,
                'bio' => $artist->bio,
                'image' => MediaUrl::url($artist->image_path),
                'cover' => MediaUrl::url($artist->cover_path),
                'is_verified' => (bool) $artist->is_verified,
                'is_featured' => (bool) $artist->is_featured,
                'social_links' => $artist->social_links ?? [],
                'church' => $artist->church ? ['name' => $artist->church->name, 'slug' => $artist->church->slug] : null,
                'region' => $artist->region?->name,
                'district' => $artist->district?->name,
            ],
            'songs' => $songs->map(fn (Song $s) => SongPayload::from($s)),
            'albums' => $albums->map(fn (Album $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'artwork' => MediaUrl::url($a->artwork_path),
                'year' => $a->release_year,
            ]),
        ]);
    }

    public function group(MusicGroup $group): Response
    {
        abort_unless($group->is_active, 404);

        $group->load([
            'church:id,name,slug',
            'region:id,name',
            'district:id,name',
            'members' => fn ($q) => $q->orderByDesc('is_leader')->orderBy('name'),
        ]);

        $songs = $group->songs()->where('status', Song::STATUS_PUBLISHED)
            ->with(['artist:id,name,slug', 'church:id,name'])
            ->orderByDesc('published_at')
            ->limit(50)->get();

        $albums = $group->albums()->where('is_published', true)
            ->orderByDesc('released_at')->limit(12)->get();

        return Inertia::render('Profiles/Group', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'type' => $group->type,
                'description' => $group->description,
                'image' => MediaUrl::url($group->image_path),
                'cover' => MediaUrl::url($group->cover_path),
                'founded_year' => $group->founded_year,
                'is_verified' => (bool) $group->is_verified,
                'is_featured' => (bool) $group->is_featured,
                'social_links' => $group->social_links ?? [],
                'church' => $group->church ? ['name' => $group->church->name, 'slug' => $group->church->slug] : null,
                'region' => $group->region?->name,
                'district' => $group->district?->name,
                'members' => $group->members->map(fn ($m) => [
                    'name' => $m->name,
                    'role' => $m->role,
                    'voice_part' => $m->voice_part,
                    'is_leader' => (bool) $m->is_leader,
                ]),
            ],
            'songs' => $songs->map(fn (Song $s) => SongPayload::from($s)),
            'albums' => $albums->map(fn (Album $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'artwork' => MediaUrl::url($a->artwork_path),
                'year' => $a->release_year,
            ]),
        ]);
    }

    public function poem(Poem $poem): Response
    {
        abort_unless($poem->status === Poem::STATUS_PUBLISHED, 404);

        $poem->load([
            'artist:id,name,stage_name,slug,image_path',
            'church:id,name,slug',
            'category:id,name,slug',
            'language:id,name',
        ]);

        $poem->increment('view_count');

        $related = Poem::published()
            ->with(['artist:id,name,stage_name,slug', 'church:id,name'])
            ->when($poem->artist_id, fn ($q) => $q->where('artist_id', $poem->artist_id))
            ->when(! $poem->artist_id && $poem->church_id, fn ($q) => $q->where('church_id', $poem->church_id))
            ->where('id', '!=', $poem->id)
            ->limit(6)->get();

        return Inertia::render('Profiles/Poem', [
            'poem' => [
                'id' => $poem->id,
                'title' => $poem->title,
                'slug' => $poem->slug,
                'summary' => $poem->summary,
                'body' => $poem->body,
                'image' => MediaUrl::url($poem->image_path),
                'document' => MediaUrl::url($poem->document_path),
                'allow_download' => (bool) $poem->allow_download,
                'is_featured' => (bool) $poem->is_featured,
                'view_count' => $poem->view_count,
                'like_count' => $poem->like_count,
                'published_at' => optional($poem->published_at)->toDateString(),
                'author' => $poem->displayAuthor(),
                'artist' => $poem->artist ? [
                    'name' => $poem->artist->stage_name ?: $poem->artist->name,
                    'slug' => $poem->artist->slug,
                    'image' => MediaUrl::url($poem->artist->image_path),
                ] : null,
                'church' => $poem->church ? [
                    'name' => $poem->church->name,
                    'slug' => $poem->church->slug,
                ] : null,
                'category' => $poem->category ? ['name' => $poem->category->name, 'slug' => $poem->category->slug] : null,
                'language' => $poem->language?->name,
            ],
            'related' => $related->map(fn (Poem $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'summary' => $p->summary,
                'image' => MediaUrl::url($p->image_path),
                'author' => $p->displayAuthor(),
            ]),
        ]);
    }

    public function church(Church $church): Response
    {
        abort_unless($church->is_active, 404);

        $church->load([
            'region:id,name',
            'district:id,name',
        ]);

        $groups = $church->musicGroups()->where('is_active', true)->orderBy('name')->limit(24)->get();
        $artists = $church->artists()->where('is_active', true)->orderBy('name')->limit(24)->get();
        $songs = $church->songs()->where('status', Song::STATUS_PUBLISHED)
            ->with(['musicGroup:id,name,slug', 'artist:id,name,slug'])
            ->orderByDesc('published_at')
            ->limit(24)->get();

        return Inertia::render('Profiles/Church', [
            'church' => [
                'id' => $church->id,
                'name' => $church->name,
                'slug' => $church->slug,
                'description' => $church->description,
                'image' => MediaUrl::url($church->image_path),
                'cover' => MediaUrl::url($church->cover_path),
                'address' => $church->address,
                'is_verified' => (bool) $church->is_verified,
                'region' => $church->region?->name,
                'district' => $church->district?->name,
            ],
            'groups' => $groups->map(fn (MusicGroup $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
                'type' => $g->type,
                'image' => MediaUrl::url($g->image_path),
            ]),
            'artists' => $artists->map(fn (Artist $a) => [
                'id' => $a->id,
                'name' => $a->stage_name ?: $a->name,
                'slug' => $a->slug,
                'image' => MediaUrl::url($a->image_path),
            ]),
            'songs' => $songs->map(fn (Song $s) => SongPayload::from($s)),
        ]);
    }
}
