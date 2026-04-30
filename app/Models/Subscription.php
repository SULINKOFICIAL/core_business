<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{

    protected $table = 'subscriptions';

    protected $casts = [
        'billing_at' => 'datetime',
        'next_billing_at' => 'datetime',
    ];

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'order_id',
        'pagarme_subscription_id',
        'pagarme_card_id',
        'interval',
        'payment_method',
        'currency',
        'installments',
        'status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TenantPlan::class, 'plan_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class, 'subscription_id', 'id');
    }
    
    public function cycles(): HasMany
    {
        return $this->hasMany(SubscriptionCycle::class, 'subscription_id', 'id');
    }

}
