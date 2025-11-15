<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientIntegration extends Model
{
    protected $table = 'clients_integrations';

    protected $fillable = [
        'client_id',
        'provider',
        'type',
        'external_account_id',
        'access_token',
        'scopes',
        'token_expires_at',
        'status',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }
}