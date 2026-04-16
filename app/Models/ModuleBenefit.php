<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleBenefit extends Model
{
    protected $fillable = [
        'module_id',
        'icon',
        'title',
        'label',
        'label_color',
        'position',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
