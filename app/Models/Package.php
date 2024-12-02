<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'value',
        'order',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
