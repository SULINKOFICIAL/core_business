<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPackageItem extends Model
{
    protected $table = 'tenants_packages_items';

    protected $fillable = [
        'package_id',
        'item_id',
        'module_name',
        'module_value',
        'billing_type',
        'payload',
        'created_at',
        'updated_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(TenantPackage::class, 'package_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'item_id', 'id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(TenantPackageItemConfiguration::class, 'item_id', 'id');
    }
}
