<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientPurchaseItem extends Model
{
    protected $table = 'clients_purchases_items';
    protected $fillable = [
        'purchase_id',
        'item_type',
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