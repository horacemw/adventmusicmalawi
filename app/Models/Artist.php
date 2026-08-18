<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Artist extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id', 'region_id', 'district_id', 'church_id',
        'name', 'slug', 'stage_name', 'bio',
        'image_path', 'cover_path', 'gender', 'phone', 'email',
        'social_links',
        'is_verified', 'is_featured', 'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function featuredOnSongs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'artist_song')->withPivot('role');
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function follows(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
