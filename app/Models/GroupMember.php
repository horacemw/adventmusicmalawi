<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    protected $fillable = [
        'music_group_id', 'user_id', 'name', 'role', 'voice_part',
        'is_leader', 'is_active', 'joined_at',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
        'is_active' => 'boolean',
        'joined_at' => 'date',
    ];

    public function musicGroup(): BelongsTo
    {
        return $this->belongsTo(MusicGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
