<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $casts = [
        'pricing_snapshot' => 'array',
        'rules_snapshot' => 'array',
        'coupon_applied_at' => 'datetime',
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
        'canceled_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'subscription_id',
        'status',
        'current_step',
        'currency',
        'total_amount',
        'pagarme_message',
        'method',
        'locked_at',
        'paid_at',
        'canceled_at',
        'expired_at',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class, 'order_id', 'id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TenantPlan::class, 'plan_id', 'id');
    }
}
