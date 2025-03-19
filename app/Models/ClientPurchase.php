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
        'type',
        'key_id',
        'previous_key_id',
        'purchase_date',
        'description',
        'method',
        'gateway_id',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(ClientPurchaseItem::class, 'purchase_id');
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'key_id');
    }

    public function previousPackage()
    {
        return $this->hasOne(Package::class, 'id', 'previous_key_id');
    }

    public function total()
    {
        return $this->items()->sum('item_value');
    }
}