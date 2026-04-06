<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRuntimeStatus extends Model
{
    protected $table = 'tenants_runtime_statuses';

    protected $fillable = [
        'tenant_id',
        'db_last_version',
        'db_error',
        'git_last_version',
        'git_error',
        'sp_last_version',
        'sp_error',
    ];

    protected $casts = [
        'db_last_version' => 'boolean',
        'git_last_version' => 'boolean',
        'sp_last_version' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
