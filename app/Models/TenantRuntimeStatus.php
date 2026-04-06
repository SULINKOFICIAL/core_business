<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRuntimeStatus extends Model
{
    protected $table = 'tenant_runtime_statuses';

    protected $fillable = [
        'client_id',
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'client_id', 'id');
    }
}
