<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantMeta extends Model
{
    protected $table = 'tenants_metas';
    protected $fillable = [
        'client_id',
        'meta_id',
        'status',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'client_id');
    }
}
