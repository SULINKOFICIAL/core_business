<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPlanItem extends Model
{
    protected $table = 'tenants_plans_items';

    protected $fillable = [
        'plan_id',
        'package_id',
        'item_id',
        'module_name',
        'module_value',
        'billing_type',
        'payload',
        'created_at',
        'updated_at',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TenantPlan::class, 'plan_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'item_id', 'id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'item_id', 'id');
    }

    public function sourcePackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(TenantPlanItemConfiguration::class, 'item_id', 'id');
    }
}
