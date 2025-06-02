<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientDomain extends Model
{

    protected $table = 'clients_domains';

    protected $fillable = [
        'client_id',
        'domain',
        'description',
        'status',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
}
