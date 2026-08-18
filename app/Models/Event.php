<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id', 'church_id', 'artist_id', 'music_group_id',
        'region_id', 'district_id',
        'title', 'slug', 'description', 'type', 'venue', 'address', 'image_path',
        'starts_at', 'ends_at', 'ticket_url',
        'is_free', 'ticket_price', 'currency',
        'is_featured', 'is_published',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'ticket_price' => 'decimal:2',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function musicGroup(): BelongsTo
    {
        return $this->belongsTo(MusicGroup::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
