<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'greeting' => $this->greeting($user->name),
            'stats' => [
                'playlists' => $user->playlists()->count(),
                'liked' => $user->likes()->where('likeable_type', Song::class)->count(),
                'following' => $user->follows()->count(),
                'submissions' => $user->submissions()->count(),
            ],
            'playlists' => $user->playlists()
                ->latest()
                ->limit(6)
                ->get(['id', 'name', 'slug', 'cover_path', 'visibility'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'cover' => $p->cover_path,
                    'visibility' => $p->visibility,
                ]),
            'likedSongs' => $user->likes()
                ->where('likeable_type', Song::class)
                ->with(['likeable' => fn ($q) => $q->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn ($l) => $l->likeable ? $this->songPayload($l->likeable) : null)
                ->filter()
                ->values(),
            'recommended' => Song::published()
                ->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])
                ->orderByDesc('stream_count')
                ->limit(6)
                ->get()
                ->map(fn (Song $s) => $this->songPayload($s)),
            'recentSubmissions' => $user->submissions()
                ->latest()
                ->limit(3)
                ->get(['id', 'reference', 'song_title', 'status', 'created_at'])
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'reference' => $s->reference,
                    'song_title' => $s->song_title ?: 'Untitled draft',
                    'status' => $s->status,
                ]),
        ]);
    }

    private function songPayload(Song $s): array
    {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'slug' => $s->slug,
            'artist' => $s->displayArtist(),
            'artwork' => $s->artwork_path,
            'duration' => $s->duration_seconds,
            'audio' => $s->audio_path,
            'streams' => $s->stream_count,
            'likes' => $s->like_count,
        ];
    }

    private function greeting(string $name): string
    {
        $hour = (int) now()->format('H');
        $when = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
        $first = trim(explode(' ', $name)[0]);
        return "{$when}, {$first}";
    }
}
