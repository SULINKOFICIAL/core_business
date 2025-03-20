<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCard extends Model
{
    protected $table = 'clients_cards';
    protected $fillable = [
        'client_id',
        'name',
        'number',
        'expiration_month',
        'expiration_year',
        'tokenization_id',
        'tokenization_id_at',
        'brand_tid',
        'brand_tid_at',
    ];
}
