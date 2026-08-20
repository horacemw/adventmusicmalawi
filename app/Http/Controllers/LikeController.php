<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Support\SongPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LikeController extends Controller
{
    public function toggle(Request $request, Song $song): JsonResponse
    {
        $user = $request->user();

        $liked = DB::transaction(function () use ($user, $song) {
            $existing = $song->likes()->where('user_id', $user->id)->first();
            if ($existing) {
                $existing->delete();
                $song->decrement('like_count');
                return false;
            }
            $song->likes()->create(['user_id' => $user->id]);
            $song->increment('like_count');
            return true;
        });

        return response()->json([
            'liked' => $liked,
            'like_count' => (int) $song->refresh()->like_count,
        ]);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        $songs = $user->likes()
            ->where('likeable_type', Song::class)
            ->with(['likeable' => fn ($q) => $q->with([
                'musicGroup:id,name,slug',
                'artist:id,name,stage_name,slug',
                'church:id,name,slug',
            ])])
            ->latest()
            ->get()
            ->map(fn ($l) => $l->likeable ? SongPayload::from($l->likeable) : null)
            ->filter()
            ->values();

        return Inertia::render('LikedSongs', [
            'songs' => $songs,
        ]);
    }
}
