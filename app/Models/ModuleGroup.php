<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleGroup extends Model
{
    protected $table = 'modules_group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_id',
        'group_id',
    ];
}
