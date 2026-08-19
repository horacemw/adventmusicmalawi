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
            'artwork' => $s->artwork_path,
            'duration' => $s->duration_seconds,
            'audio' => $s->audio_path,
            'streams' => $s->stream_count,
            'likes' => $s->like_count,
            'rank' => $rank,
        ];
    }
}
