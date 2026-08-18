<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Church extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'churches';

    protected $fillable = [
        'region_id', 'district_id', 'name', 'slug', 'description',
        'image_path', 'cover_path', 'address', 'phone', 'email', 'website',
        'is_verified', 'is_featured', 'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    public function musicGroups(): HasMany
    {
        return $this->hasMany(MusicGroup::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
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
