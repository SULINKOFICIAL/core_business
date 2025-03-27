<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $casts = [
        'paid_at' => 'datetime',
    ];
    protected $fillable = [
        'client_id',
        'type',
        'key_id',
        'previous_key_id',
        'paid_at',
        'description',
        'status',
    ];

    public function paidBy()
    {
        return $this->transactions()->where('status', 'Pago')->first();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function transactions()
    {
        return $this->hasMany(OrderTransaction::class, 'order_id');
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