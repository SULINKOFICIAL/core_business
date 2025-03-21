<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientSubscription extends Model
{
    protected $table = 'client_subscriptions';
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

    // Relacionamento com resources
    public function order(): HasOne
    {
       return $this->hasOne(Order::class, 'id', 'order_id');
    }

}