<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    // Turn a disk-relative path (songs/audio/foo.mp3) into a browser-usable URL.
    // Already-absolute URLs and root-relative paths (starting with "/") pass through.
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }
}
