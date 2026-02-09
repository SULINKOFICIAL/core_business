<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemConfiguration extends Model
{
    protected $table = 'order_item_configurations';

    protected $casts = [
        'derived_pricing_effect' => 'array',
    ];

    protected $fillable = [
        'order_item_id',
        'key',
        'value',
        'value_type',
        'derived_pricing_effect',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
