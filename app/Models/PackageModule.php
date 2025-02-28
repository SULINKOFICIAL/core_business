<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageModule extends Model
{
    protected $table = 'packages_modules';
    protected $fillable = [
        'module_id',
        'package_id',
        'filed_by',
        'created_by',
        'updated_by',
    ];
}
