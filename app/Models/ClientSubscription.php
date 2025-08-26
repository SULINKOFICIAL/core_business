<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientSubscription extends Model
{
    protected $table = 'clients_subscriptions';
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    
    protected $fillable = [
        'client_id',
        'package_id',
        'order_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function order(): HasOne
    {
       return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function client(): HasOne
    {
       return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function package(): HasOne
    {
       return $this->hasOne(Package::class, 'id', 'package_id');
    }

}