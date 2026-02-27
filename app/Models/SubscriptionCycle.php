<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionCycle extends Model
{
    protected $table = 'subscriptions_cycles';

    protected $fillable = [
        'subscription_id',
        'pagarme_cycle_id',
        'start_date',
        'end_date',
        'status',
        'cycle',
        'billing_at',
        'next_billing_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'billing_at' => 'datetime',
        'next_billing_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}
