<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupResource extends Model
{
    protected $table = 'group_resource';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'resource_id',
    ];
}
