<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class HymnBook extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'language_id', 'description', 'publisher',
        'published_year', 'cover_path', 'copyright_notice', 'license_type',
        'is_public_domain', 'is_active',
    ];

    protected $casts = [
        'is_public_domain' => 'boolean',
        'is_active' => 'boolean',
        'published_year' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function hymns(): HasMany
    {
        return $this->hasMany(Hymn::class)->orderBy('number');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
