<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientModule extends Model
{
    protected $table = 'clients_modules';
    protected $fillable = [
        'client_id',
        'module_id',
    ];
}
