<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageBenefit extends Model
{
    protected $fillable = [
        'package_id',
        'icon',
        'title',
        'label',
        'label_color',
        'position',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
