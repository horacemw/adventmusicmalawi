<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class District extends Model
{
    use HasSlug;

    protected $fillable = ['region_id', 'name', 'slug'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function churches(): HasMany
    {
        return $this->hasMany(Church::class);
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    public function musicGroups(): HasMany
    {
        return $this->hasMany(MusicGroup::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
