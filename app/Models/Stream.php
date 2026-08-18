<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stream extends Model
{
    protected $fillable = [
        'song_id', 'user_id', 'session_id', 'ip_hash',
        'country', 'region', 'device_type', 'platform', 'browser',
        'duration_played_seconds', 'completed', 'counted',
        'started_at', 'ended_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'counted' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_played_seconds' => 'integer',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
