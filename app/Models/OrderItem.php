<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $table = 'orders_items';

    protected $casts = [
        'pricing_model_snapshot' => 'array',
        'rules_snapshot' => 'array',
    ];

    protected $fillable = [
        'order_id',
        'type',
        'item_name',
        'item_key',
        'item_billing_type',
        'item_usage_quantity',
        'item_value',
        'amount',
        'payload',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(OrderItemConfiguration::class, 'order_item_id');
    }
}
