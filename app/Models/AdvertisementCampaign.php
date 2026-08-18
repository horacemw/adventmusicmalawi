<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisementCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'advertiser_name', 'advertiser_email', 'advertiser_phone',
        'description', 'budget', 'currency', 'status', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }
}
