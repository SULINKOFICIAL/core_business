<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'name',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
