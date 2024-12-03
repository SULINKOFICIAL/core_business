<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    public function resources(): BelongsToMany
    {
       return $this->belongsToMany(Resource::class, 'group_resource');
    }
}
