<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function song(Request $request, Song $song): StreamedResponse
    {
        abort_unless($song->status === Song::STATUS_PUBLISHED, 404);
        abort_unless($song->allow_download, 403, 'Downloads are disabled for this song.');
        abort_unless($song->audio_path, 404);

        // Record the download (independent of stream count)
        Download::create([
            'song_id' => $song->id,
            'user_id' => $request->user()?->id,
            'ip_hash' => hash('sha256', $request->ip().'|'.($request->header('User-Agent') ?? '')),
            'platform' => substr($request->header('User-Agent', ''), 0, 32) ?: null,
        ]);
        $song->increment('download_count');

        $relative = ltrim(parse_url($song->audio_path, PHP_URL_PATH) ?? $song->audio_path, '/');
        $relative = preg_replace('#^storage/#', '', $relative);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($relative), 404);

        $filename = str($song->title)->slug()->append('.'.pathinfo($relative, PATHINFO_EXTENSION))->toString();
        return $disk->download($relative, $filename);
    }
}
