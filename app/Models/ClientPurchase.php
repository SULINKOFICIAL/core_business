<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPurchase extends Model
{
    protected $table = 'clients_purchases';
    protected $casts = [
        'purchase_date' => 'datetime',
    ];
    protected $fillable = [
        'client_id',
        'purchase_date',
        'previous_value',
        'total_value',
        'description',
        'method',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(ClientPurchaseItem::class, 'purchase_id');
    }
}