<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $fillable = [
        'name',
        'color',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
