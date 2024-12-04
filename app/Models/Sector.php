<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sector extends Model
{
    protected $fillable = [
        'name',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    // Relacionamento com groups
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'sector_group');
    }
}
