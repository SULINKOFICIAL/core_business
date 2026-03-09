<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDomain extends Model
{

    protected $table = 'clients_domains';

    protected $fillable = [
        'client_id',
        'domain',
        'auto_generate',
        'description',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
