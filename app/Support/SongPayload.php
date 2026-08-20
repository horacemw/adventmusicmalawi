<?php

namespace App\Support;

use App\Models\Song;

class SongPayload
{
    public static function from(Song $s, ?int $rank = null): array
    {
        return [
            'id' => $s->id,
            'title' => $s->title ?? '—',
            'slug' => $s->slug,
            'artist' => $s->displayArtist(),
            'artwork' => MediaUrl::url($s->artwork_path),
            'duration' => $s->duration_seconds,
            'audio' => MediaUrl::url($s->audio_path),
            'streams' => $s->stream_count,
            'likes' => $s->like_count,
            'allow_download' => (bool) $s->allow_download,
            'rank' => $rank,
        ];
    }
}
