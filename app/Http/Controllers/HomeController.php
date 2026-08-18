<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Category;
use App\Models\MusicGroup;
use App\Models\Occasion;
use App\Models\Song;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'hero' => [
                'title' => 'Discover the Adventist Sound of Malawi',
                'subtitle' => 'Thousands of songs from churches, choirs and music groups across Malawi.',
                'cta' => ['label' => 'Explore Music', 'href' => '/discover'],
            ],
            'chips' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(9)
                ->get(['id', 'name', 'slug', 'icon', 'color'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon' => $c->icon,
                    'color' => $c->color,
                ]),
            'newReleases' => Song::published()
                ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
                ->orderByDesc('published_at')
                ->limit(6)
                ->get()
                ->map(fn (Song $s) => $this->songPayload($s)),
            'topSongs' => Song::published()
                ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
                ->orderByDesc('stream_count')
                ->limit(8)
                ->get()
                ->map(fn (Song $s, int $idx) => $this->songPayload($s, $idx + 1)),
            'occasions' => Occasion::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get(['id', 'name', 'slug', 'image_path'])
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'slug' => $o->slug,
                    'image' => $o->image_path,
                ]),
            'trending' => Song::published()
                ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
                ->orderByDesc('like_count')
                ->limit(6)
                ->get()
                ->map(fn (Song $s, int $idx) => $this->songPayload($s, $idx + 1)),
            'featuredGroups' => MusicGroup::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->limit(6)
                ->get(['id', 'name', 'slug', 'type', 'image_path'])
                ->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'slug' => $g->slug,
                    'type' => $g->type,
                    'image' => $g->image_path,
                ]),
            'nowPlaying' => (function () {
                $s = Song::published()
                    ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
                    ->where('is_featured', true)
                    ->orderByDesc('published_at')
                    ->first()
                    ?? Song::published()->orderByDesc('stream_count')->first();

                return $s ? $this->songPayload($s) : null;
            })(),
        ]);
    }

    private function songPayload(Song $s, ?int $rank = null): array
    {
        return [
            'id' => $s->id,
            'title' => $s->title ?? '—',
            'slug' => $s->slug,
            'artist' => $s->displayArtist(),
            'artwork' => $s->artwork_path,
            'duration' => $s->duration_seconds,
            'audio' => $s->audio_path,
            'streams' => $s->stream_count,
            'likes' => $s->like_count,
            'rank' => $rank,
        ];
    }
}
