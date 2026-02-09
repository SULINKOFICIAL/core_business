<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTransaction extends Model
{
    protected $table = 'order_transactions';

    protected $casts = [
        'raw_response_snapshot' => 'array',
        'authorized_at' => 'datetime',
        'paid_at' => 'datetime',
        'brand_tid_at' => 'datetime',
    ];

    protected $fillable = [
        'order_id',
        'gateway_id',
        'gateway_code',
        'external_transaction_id',
        'status',
        'amount',
        'currency',
        // Legacy
        'method',
        'brand_tid_at',
        'brand_tid',
        'response',
        'raw_response_snapshot',
        'authorized_at',
        'paid_at',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }
}
