<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Hymn extends Model
{
    use HasSlug;

    protected $fillable = [
        'hymn_book_id', 'number', 'title', 'slug', 'lyrics',
        'description', 'author', 'composer', 'tune_name', 'meter',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (Hymn $h) => $h->number.'-'.$h->title)
            ->saveSlugsTo('slug');
    }

    public function hymnBook(): BelongsTo
    {
        return $this->belongsTo(HymnBook::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(HymnRecording::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
