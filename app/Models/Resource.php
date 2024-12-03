<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'name',
        'slug',
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
}