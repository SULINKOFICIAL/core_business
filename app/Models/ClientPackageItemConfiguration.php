<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPackageItemConfiguration extends Model
{
    protected $table = 'clients_packages_items_configurations';

    protected $casts = [
        'derived_pricing_effect' => 'array',
    ];

    protected $fillable = [
        'item_id',
        'key',
        'value',
        'value_type',
        'derived_pricing_effect',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ClientPackageItem::class, 'item_id', 'id');
    }
}
