<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    protected $fillable = [
        'advertisement_campaign_id', 'title', 'body', 'cta_label', 'cta_url',
        'image_path', 'placement', 'sort_order', 'is_active', 'impressions', 'clicks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'sort_order' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'advertisement_campaign_id');
    }
}
