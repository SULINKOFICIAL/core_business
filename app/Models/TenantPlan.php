<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TenantPlan extends Model
{
    protected $table = 'tenants_plans';

    protected $fillable = [
        'tenant_id',
        'name',
        'value',
        'progress',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TenantPlanItem::class, 'plan_id', 'id');
    }

    public function modules(): HasManyThrough
    {
        return $this->hasManyThrough(
            Module::class,
            TenantPlanItem::class,
            'plan_id',
            'id',
            'id',
            'item_id',
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'plan_id', 'id');
    }
}
