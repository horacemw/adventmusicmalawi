<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StreamController extends Controller
{
    /**
     * Record a playback event from the frontend player.
     *
     * A stream is only "counted" (increments Song.stream_count) if:
     *  - Payload declares at least MIN_COUNTED_SECONDS of playback, AND
     *  - We haven't already counted this (song, session) pair in the last hour.
     *
     * Everything else is stored as an uncounted analytics row so we still
     * capture skips/seeks/pause behavior for later analysis.
     */
    private const MIN_COUNTED_SECONDS = 20;
    private const DEDUPE_WINDOW_SECONDS = 3600;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'song_id' => 'required|integer|exists:songs,id',
            'duration_played' => 'nullable|integer|min:0|max:86400',
            'completed' => 'nullable|boolean',
        ]);

        $duration = (int) ($data['duration_played'] ?? 0);
        $completed = (bool) ($data['completed'] ?? false);
        $song = Song::find($data['song_id']);
        if (! $song) {
            return response()->json(['ok' => false], 404);
        }

        $userId = $request->user()?->id;
        $sessionId = $request->session()->getId();
        $ipHash = hash('sha256', $request->ip() . '|' . config('app.key'));

        // De-dupe: don't count the same session playing the same song multiple
        // times inside an hour. This survives pause/resume within a listen.
        $dedupeKey = 'stream:count:' . $song->id . ':' . ($userId ?: $sessionId);
        $alreadyCounted = Cache::has($dedupeKey);
        $shouldCount = $duration >= self::MIN_COUNTED_SECONDS && ! $alreadyCounted;

        Stream::create([
            'song_id' => $song->id,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_hash' => $ipHash,
            'device_type' => $this->deviceType($request->userAgent()),
            'platform' => $this->platform($request->userAgent()),
            'browser' => $this->browser($request->userAgent()),
            'duration_played_seconds' => $duration,
            'completed' => $completed,
            'counted' => $shouldCount,
            'started_at' => now()->subSeconds($duration),
            'ended_at' => now(),
        ]);

        if ($shouldCount) {
            $song->increment('stream_count');
            Cache::put($dedupeKey, true, self::DEDUPE_WINDOW_SECONDS);
        }

        return response()->json(['ok' => true, 'counted' => $shouldCount]);
    }

    private function deviceType(?string $ua): string
    {
        if (! $ua) return 'unknown';
        return match (true) {
            str_contains($ua, 'Mobile') || str_contains($ua, 'Android') => 'mobile',
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'tablet',
            default => 'desktop',
        };
    }

    private function platform(?string $ua): string
    {
        if (! $ua) return 'unknown';
        return match (true) {
            str_contains($ua, 'Windows') => 'windows',
            str_contains($ua, 'Android') => 'android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'ios',
            str_contains($ua, 'Mac OS') => 'macos',
            str_contains($ua, 'Linux') => 'linux',
            default => 'other',
        };
    }

    private function browser(?string $ua): string
    {
        if (! $ua) return 'unknown';
        return match (true) {
            str_contains($ua, 'Edg/') => 'edge',
            str_contains($ua, 'Chrome/') => 'chrome',
            str_contains($ua, 'Firefox/') => 'firefox',
            str_contains($ua, 'Safari/') => 'safari',
            default => 'other',
        };
    }
}
