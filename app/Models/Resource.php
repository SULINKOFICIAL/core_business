<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = [
        'module_id',
        'name',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    // Relacionamento com Group
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_resource');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }
}