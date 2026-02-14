<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSubscription extends Model
{

    protected $table = 'orders_subscriptions';

    protected $fillable = [
        'order_id',
        'pagarme_subscription_id',
        'pagarme_card_id',
        'interval',
        'payment_method',
        'currency',
        'installments',
        'status',
        'billing_at',
        'next_billing_at',
    ];

}
