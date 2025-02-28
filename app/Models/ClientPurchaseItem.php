<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPurchaseItem extends Model
{
    protected $table = 'clients_purchases_items';
    protected $fillable = [
        'purchase_id',
        'item_type',
        'item_name',
        'quantity',
        'item_value',
        'start_date',
        'end_date',
    ];
}