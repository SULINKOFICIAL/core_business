<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'package_id',
        'subscription_id',
        'status',
        'current_step',
        'currency',
        'total_amount',
        'pagarme_message',
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

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderTransaction::class,
            Subscription::class,
            'order_id',
            'subscription_id',
            'id',
            'id'
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class, 'package_id', 'id');
    }

    public function previousPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'previous_key_id');
    }
}
