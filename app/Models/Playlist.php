<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Playlist extends Model
{
    use HasSlug, SoftDeletes;

    public const VIS_PRIVATE = 'private';
    public const VIS_PUBLIC = 'public';
    public const VIS_UNLISTED = 'unlisted';

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'cover_path',
        'visibility', 'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VIS_PUBLIC);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'playlist_song')
            ->withPivot(['position', 'added_at'])
            ->orderBy('pivot_position');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
