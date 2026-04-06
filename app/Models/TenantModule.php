<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $table = 'tenants_modules';
    protected $fillable = [
        'client_id',
        'module_id',
    ];
}
