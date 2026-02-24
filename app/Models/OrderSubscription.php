<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderSubscription extends Model
{

    protected $table = 'orders_subscriptions';

    protected $casts = [
        'billing_at' => 'datetime',
        'next_billing_at' => 'datetime',
    ];

    protected $fillable = [
        'order_id',
        'pagarme_subscription_id',
        'pagarme_card_id',
        'interval',
        'payment_method',
        'currency',
        'installments',
        'status',
        'billing_at',
        'next_billing_at',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class, 'subscription_id');
    }
}
