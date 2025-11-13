<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMeta extends Model
{
    protected $table = 'clients_metas';
    protected $fillable = [
        'client_id',
        'meta_id',
        'status',
    ];
}
