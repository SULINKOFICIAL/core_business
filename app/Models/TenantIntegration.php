<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantIntegration extends Model
{
    protected $table = 'tenants_integrations';

    protected $fillable = [
        'tenant_id',
        'provider',
        'client_provider_id',
        'temporary',
        'type',
        'external_account_id',
        'access_token',
        'scopes',
        'token_expires_at',
        'status',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function meta(): HasOne
    {
        return $this->hasOne(TenantMeta::class, 'id', 'client_provider_id');
    }
}