<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPackageItem extends Model
{
    protected $table = 'clients_packages_items';

    protected $fillable = [
        'package_id',
        'item_id',
        'created_at',
        'updated_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class, 'package_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'item_id', 'id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(ClientPackageItemConfiguration::class, 'item_id', 'id');
    }
}
