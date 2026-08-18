<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Album extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'artist_id', 'music_group_id', 'church_id', 'primary_language_id',
        'title', 'slug', 'description', 'artwork_path',
        'release_year', 'released_at', 'label',
        'is_featured', 'is_published',
    ];

    protected $casts = [
        'released_at' => 'date',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'release_year' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
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

    public function primaryLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'primary_language_id');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->orderBy('track_number');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
