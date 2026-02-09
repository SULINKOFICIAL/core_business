<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSubscriptionItem extends Model
{
    protected $table = 'client_subscription_items';

    protected $casts = [
        'current_config_snapshot' => 'array',
        'current_price_snapshot' => 'array',
    ];

    protected $fillable = [
        'client_subscription_id',
        'module_id',
        'module_code',
        'status',
        'current_config_snapshot',
        'current_price_snapshot',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ClientSubscription::class, 'client_subscription_id');
    }
}
