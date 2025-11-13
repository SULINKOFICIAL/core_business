<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientMeta extends Model
{
    protected $table = 'clients_metas';
    protected $fillable = [
        'client_id',
        'meta_id',
        'status',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
}
