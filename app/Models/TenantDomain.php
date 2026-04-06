<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{

    protected $table = 'tenants_domains';

    protected $fillable = [
        'client_id',
        'domain',
        'auto_generate',
        'description',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'client_id', 'id');
    }
}
