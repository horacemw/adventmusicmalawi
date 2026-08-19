<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Submission extends Model
{
    use SoftDeletes;

    public const KIND_SONG = 'song';
    public const KIND_ALBUM = 'album';
    public const KIND_POEM = 'poem';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'reference', 'kind', 'user_id',
        'submitter_name', 'submitter_email', 'submitter_phone',
        'song_title', 'artist_name', 'group_name', 'choir_name', 'church_name', 'album_title',
        'release_year', 'description',
        'language_id', 'genre_id', 'region_id', 'district_id',
        'artist_id', 'music_group_id', 'church_id',
        'copyright_owner', 'rights_holder', 'permission_status',
        'owner_confirmation', 'platform_distribution_permission', 'accuracy_confirmation',
        'copyright_notes', 'status', 'rejection_reason', 'reviewer_notes',
        'reviewer_id', 'reviewed_at', 'song_id', 'published_at',
    ];

    protected $casts = [
        'owner_confirmation' => 'boolean',
        'platform_distribution_permission' => 'boolean',
        'accuracy_confirmation' => 'boolean',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'release_year' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Submission $submission) {
            $submission->reference ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function musicGroup(): BelongsTo
    {
        return $this->belongsTo(MusicGroup::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'submission_category');
    }

    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Occasion::class, 'submission_occasion');
    }

    public function moods(): BelongsToMany
    {
        return $this->belongsToMany(Mood::class, 'submission_mood');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubmissionReview::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
