<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HymnRecording extends Model
{
    protected $fillable = ['hymn_id', 'song_id', 'notes'];

    public function hymn(): BelongsTo
    {
        return $this->belongsTo(Hymn::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
