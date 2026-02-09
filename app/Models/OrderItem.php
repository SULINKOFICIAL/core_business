<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $casts = [
        'pricing_model_snapshot' => 'array',
        'rules_snapshot' => 'array',
    ];

    protected $fillable = [
        'order_id',
        'item_type',
        'action',
        'item_code',
        'item_name_snapshot',
        'item_reference_type',
        'item_reference_id',
        'quantity',
        'unit_price_snapshot',
        'subtotal_amount',
        'pricing_model_snapshot',
        'rules_snapshot',
        // Legacy
        'type',
        'item_name',
        'item_key',
        'item_value',
        'amount',
        'start_date',
        'end_date',
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
