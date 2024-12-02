<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
