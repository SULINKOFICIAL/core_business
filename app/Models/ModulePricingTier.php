<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulePricingTier extends Model
{
    protected $fillable = [
        'module_id',
        'usage_limit',
        'price',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
