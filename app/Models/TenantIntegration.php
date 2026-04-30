<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantIntegration extends Model
{
    protected $table = 'tenants_integrations';

    protected $casts = [
        'token_expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
    ];

    protected $fillable = [
        'tenant_id',
        'provider',
        'client_provider_id',
        'temporary',
        'type',
        'external_account_id',
        'access_token',
        'refresh_token',
        'scopes',
        'token_expires_at',
        'refresh_expires_at',
        'last_renewed_at',
        'status',
    ];

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function meta(): HasOne
    {
        return $this->hasOne(TenantMeta::class, 'id', 'client_provider_id');
    }
}