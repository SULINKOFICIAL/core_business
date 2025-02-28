<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPurchase extends Model
{
    protected $table = 'clients_purchases';
    protected $fillable = [
        'client_id',
        'purchase_date',
        'total_value',
        'method',
        'status',
    ];
}