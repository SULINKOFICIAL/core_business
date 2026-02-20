<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'client_id',
        'pagarme_plan_id',
        'status',
        'current_step',
        'currency',
        'total_amount',
        'type',
        'key_id',
        'previous_key_id',
        'method',
        'description',
        'locked_at',
        'paid_at',
        'canceled_at',
        'expired_at',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderTransaction::class,
            OrderSubscription::class,
            'order_id',
            'subscription_id',
            'id',
            'id'
        );
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(OrderSubscription::class, 'order_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'key_id');
    }

    public function previousPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'previous_key_id');
    }

}
