<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'start_date',
        'end_date',
        'status',
    ];
}