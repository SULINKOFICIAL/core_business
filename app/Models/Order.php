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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected $fillable = [
        'client_id',
        'pagarme_plan_id',
        'status',
        'current_step',
        'currency',
        'total_amount',
        'coupon_discount_amount',
        'pagarme_message',
        'pricing_snapshot',
        'rules_snapshot',
        'coupon_id',
        'coupon_code_snapshot',
        'coupon_type_snapshot',
        'coupon_value_snapshot',
        'coupon_trial_months',
        'coupon_applied_at',
        'type',
        'key_id',
        'previous_key_id',
        'method',
        'description',
        'locked_at',
        'paid_at',
        'canceled_at',
        'start_date',
        'end_date',
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

    public function total(): float
    {
        if (!is_null($this->total_amount) && (float) $this->total_amount !== 0.0) {
            return (float) $this->total_amount;
        }

        $subtotal = (float) $this->items()->sum('subtotal_amount');
        if ($subtotal > 0) {
            $discount = (float) ($this->coupon_discount_amount ?? 0);
            $total = $subtotal - $discount;
            return $total > 0 ? $total : 0.0;
        }

        $legacyTotal = (float) $this->items()->sum('item_value');
        if ($legacyTotal !== 0.0) {
            return $legacyTotal;
        }

        return (float) $this->items()->sum('amount');
    }
}
