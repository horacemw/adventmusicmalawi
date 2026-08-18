<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Genre extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
