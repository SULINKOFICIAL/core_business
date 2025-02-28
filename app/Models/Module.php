<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'description',
        'value',
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
}
