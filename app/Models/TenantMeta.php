<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantMeta extends Model
{
    protected $table = 'tenants_metas';
    protected $fillable = [
        'tenant_id',
        'meta_id',
        'status',
    ];

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}
