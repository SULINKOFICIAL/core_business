<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantCard extends Model
{
    protected $table = 'tenants_cards';
    protected $fillable = [
        'client_id',
        'main',
        'name',
        'number',
        'expiration_month',
        'expiration_year',
        'tokenization_id',
        'tokenization_id_at',
        'brand_tid',
        'brand_tid_at',
        'pagarme_card_id',
    ];
}
