<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPackage extends Model
{
    protected $table = 'clients_packages';
    protected $fillable = [
        'client_id',
        'package_id',
        'start_date',
        'end_date',
        'status',
    ];
}