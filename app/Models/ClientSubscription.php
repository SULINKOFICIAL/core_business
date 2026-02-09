<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientSubscription extends Model
{
    protected $table = 'clients_subscriptions';
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_at' => 'datetime',
        'canceled_at' => 'datetime',
        'paused_at' => 'datetime',
    ];
    
    protected $fillable = [
        'client_id',
        'package_id',
        'order_id',
        'start_date',
        'end_date',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'next_billing_at',
        'canceled_at',
        'paused_at',
    ];

    public function order(): BelongsTo
    {
       return $this->belongsTo(Order::class, 'order_id');
    }

    public function client(): BelongsTo
    {
       return $this->belongsTo(Client::class, 'client_id');
    }

    public function package(): BelongsTo
    {
       return $this->belongsTo(Package::class, 'package_id');
    }

    public function items(): HasMany
    {
       return $this->hasMany(ClientSubscriptionItem::class, 'client_subscription_id');
    }

}
