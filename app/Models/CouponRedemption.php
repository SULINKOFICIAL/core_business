<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    protected $table = 'coupon_redemptions';

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    protected $fillable = [
        'coupon_id',
        'order_id',
        'client_id',
        'redeemed_at',
        'amount_discounted',
        'currency',
        'code_snapshot',
        'type_snapshot',
        'value_snapshot',
        'trial_months_snapshot',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
