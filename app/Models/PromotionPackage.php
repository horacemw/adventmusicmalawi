<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PromotionPackage extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'description', 'placement',
        'duration_days', 'price', 'currency', 'perks', 'is_active',
    ];

    protected $casts = [
        'perks' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration_days' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
