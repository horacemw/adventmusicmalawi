<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public const EVENT_INITIATE = 'initiate';
    public const EVENT_CALLBACK = 'callback';
    public const EVENT_VERIFY = 'verify';
    public const EVENT_REFUND = 'refund';
    public const EVENT_WEBHOOK = 'webhook';

    protected $fillable = ['payment_id', 'event_type', 'status', 'payload', 'ip_address'];

    protected $casts = [
        'payload' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
