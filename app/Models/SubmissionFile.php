<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    public const KIND_AUDIO = 'audio';
    public const KIND_ARTWORK = 'artwork';
    public const KIND_ARTIST_IMAGE = 'artist_image';
    public const KIND_PERMISSION = 'permission_document';
    public const KIND_OTHER = 'other';

    protected $fillable = [
        'submission_id', 'kind', 'original_name',
        'storage_path', 'mime_type', 'size_bytes', 'checksum_sha256',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
