<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalUser extends Model
{
    protected $fillable = [
        'quantity',
        'price',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
