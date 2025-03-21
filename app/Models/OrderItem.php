<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $table = 'orders_items';
    protected $fillable = [
        'order_id',
        'type',
        'action',
        'item_name',
        'item_key',
        'quantity',
        'item_value',
    ];

    // Relacionamento com resources
    public function module(): HasOne
    {
       return $this->hasOne(Module::class, 'id', 'item_key');
    }
}