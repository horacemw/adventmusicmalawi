<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Song extends Model
{
    use HasSlug, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'artist_id', 'music_group_id', 'church_id', 'album_id',
        'language_id', 'genre_id', 'uploader_id',
        'title', 'slug', 'description', 'lyrics',
        'audio_path', 'audio_format', 'duration_seconds', 'audio_size_bytes',
        'artwork_path', 'release_year', 'released_at',
        'status',
        'stream_count', 'like_count', 'share_count', 'download_count',
        'is_featured', 'allow_download', 'explicit_content',
        'track_number', 'published_at',
    ];

    protected $casts = [
        'released_at' => 'date',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_download' => 'boolean',
        'explicit_content' => 'boolean',
        'duration_seconds' => 'integer',
        'audio_size_bytes' => 'integer',
        'stream_count' => 'integer',
        'like_count' => 'integer',
        'share_count' => 'integer',
        'download_count' => 'integer',
        'release_year' => 'integer',
        'track_number' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending(Builder $query, int $days = 7): Builder
    {
        return $query->withCount(['streams' => fn ($q) => $q->where('created_at', '>=', now()->subDays($days))->where('counted', true)])
            ->orderByDesc('streams_count');
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

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_song');
    }

    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Occasion::class, 'occasion_song');
    }

    public function moods(): BelongsToMany
    {
        return $this->belongsToMany(Mood::class, 'mood_song');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'song_tag');
    }

    public function featuredArtists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_song')->withPivot('role');
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    public function copyright(): HasOne
    {
        return $this->hasOne(SongCopyright::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function hymnRecording(): HasOne
    {
        return $this->hasOne(HymnRecording::class);
    }

    public function displayArtist(): string
    {
        if ($this->musicGroup) {
            return $this->musicGroup->name;
        }
        if ($this->artist) {
            return $this->artist->stage_name ?? $this->artist->name;
        }
        return $this->church?->name ?? 'Unknown';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
