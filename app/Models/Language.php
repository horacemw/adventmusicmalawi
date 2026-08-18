<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Language extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'iso_code', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'primary_language_id');
    }

    public function hymnBooks(): HasMany
    {
        return $this->hasMany(HymnBook::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
