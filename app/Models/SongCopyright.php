<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongCopyright extends Model
{
    protected $fillable = [
        'song_id', 'copyright_owner', 'rights_holder',
        'permission_status', 'license_type',
        'distribution_allowed', 'monetization_allowed',
        'permission_document_path', 'notes',
    ];

    protected $casts = [
        'distribution_allowed' => 'boolean',
        'monetization_allowed' => 'boolean',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
