<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'value',
        'pricing_type',
        'usage_label',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    // Relacionamento com groups
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'modules_group');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'packages_modules', 'module_id', 'package_id');
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(ModulePricingTier::class);
    }

}
