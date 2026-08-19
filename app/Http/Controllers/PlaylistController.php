<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Playlists/Index', [
            'playlists' => $user->playlists()
                ->withCount('songs')
                ->latest()
                ->get(['id', 'name', 'slug', 'description', 'cover_path', 'visibility', 'is_pinned', 'created_at'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'cover' => $p->cover_path,
                    'visibility' => $p->visibility,
                    'is_pinned' => (bool) $p->is_pinned,
                    'song_count' => $p->songs_count,
                    'created_at' => $p->created_at->diffForHumans(),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Playlists/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:private,public,unlisted',
        ]);

        $playlist = $request->user()->playlists()->create($data);

        return redirect()->route('playlists.show', $playlist);
    }

    public function show(Request $request, Playlist $playlist): Response
    {
        $this->authorizeAccess($request, $playlist);

        $playlist->load(['songs' => fn ($q) => $q->with(['musicGroup:id,name,slug', 'artist:id,name,slug', 'church:id,name'])]);

        return Inertia::render('Playlists/Show', [
            'playlist' => [
                'id' => $playlist->id,
                'name' => $playlist->name,
                'description' => $playlist->description,
                'cover' => $playlist->cover_path,
                'visibility' => $playlist->visibility,
                'is_owner' => $playlist->user_id === $request->user()?->id,
                'created_at' => $playlist->created_at->format('d M Y'),
                'songs' => $playlist->songs->map(fn (Song $s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'slug' => $s->slug,
                    'artist' => $s->displayArtist(),
                    'artwork' => $s->artwork_path,
                    'duration' => $s->duration_seconds,
                    'audio' => $s->audio_path,
                    'streams' => $s->stream_count,
                    'likes' => $s->like_count,
                    'position' => $s->pivot->position,
                ]),
            ],
        ]);
    }

    public function update(Request $request, Playlist $playlist): RedirectResponse
    {
        $this->authorizeOwn($request, $playlist);

        $playlist->update($request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'visibility' => 'sometimes|required|in:private,public,unlisted',
        ]));

        return back()->with('success', 'Playlist updated');
    }

    public function destroy(Request $request, Playlist $playlist): RedirectResponse
    {
        $this->authorizeOwn($request, $playlist);
        $playlist->delete();

        return redirect()->route('playlists.index')->with('success', 'Playlist deleted');
    }

    public function addSong(Request $request, Playlist $playlist): RedirectResponse
    {
        $this->authorizeOwn($request, $playlist);

        $data = $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $position = (int) $playlist->songs()->max('playlist_song.position') + 1;
        $playlist->songs()->syncWithoutDetaching([
            $data['song_id'] => ['position' => $position, 'added_at' => now()],
        ]);

        return back()->with('success', 'Added');
    }

    public function removeSong(Request $request, Playlist $playlist, Song $song): RedirectResponse
    {
        $this->authorizeOwn($request, $playlist);
        $playlist->songs()->detach($song->id);

        return back()->with('success', 'Removed');
    }

    private function authorizeAccess(Request $request, Playlist $playlist): void
    {
        if ($playlist->user_id === $request->user()?->id) {
            return;
        }
        abort_unless(in_array($playlist->visibility, [Playlist::VIS_PUBLIC, Playlist::VIS_UNLISTED], true), 404);
    }

    private function authorizeOwn(Request $request, Playlist $playlist): void
    {
        abort_unless($playlist->user_id === $request->user()?->id, 403);
    }
}
