<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderTransaction extends Model
{
    protected $table = 'orders_transactions';
    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'gateway_id',
        'brand_tid',
        'brand_tid_at',
        'response',
    ];

    // Relacionamento com resources
    public function order(): HasOne
    {
       return $this->hasOne(Order::class, 'id', 'order_id');
    }
    // Relacionamento com resources
    public function gateway(): HasOne
    {
       return $this->hasOne(Gateway::class, 'id', 'order_id');
    }
}
