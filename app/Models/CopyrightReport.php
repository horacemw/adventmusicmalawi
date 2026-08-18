<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CopyrightReport extends Model
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'reference', 'reporter_user_id',
        'reporter_name', 'reporter_email', 'reporter_phone', 'reporter_organization',
        'target_id', 'target_type',
        'claim', 'evidence_path', 'status',
        'assigned_to', 'resolution_notes', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CopyrightReport $r) {
            $r->reference ??= (string) Str::uuid();
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
