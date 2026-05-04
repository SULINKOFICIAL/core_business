<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    protected $table = 'tenants_subscriptions';
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_at' => 'datetime',
        'canceled_at' => 'datetime',
        'paused_at' => 'datetime',
    ];
    
    protected $fillable = [
        'tenant_id',
        'order_id',
        'start_date',
        'end_date',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'next_billing_at',
        'canceled_at',
        'paused_at',
    ];

    public function order(): BelongsTo
    {
       return $this->belongsTo(Order::class, 'order_id');
    }

    public function tenant(): BelongsTo
    {
       return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function items(): HasMany
    {
       return $this->hasMany(TenantSubscriptionItem::class, 'tenant_subscription_id');
    }

}
