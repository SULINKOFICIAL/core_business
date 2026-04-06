<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProvisioning extends Model
{
    public const STEP_SUBDOMAIN = 'subdomain';
    public const STEP_DATABASE = 'database';
    public const STEP_USER_TOKEN = 'user_token';
    public const STEP_MODULES = 'modules';
    public const STEP_FINALIZING = 'finalizing';
    public const STEP_COMPLETED = 'completed';

    public const INSTALL_STEPS = [
        self::STEP_SUBDOMAIN,
        self::STEP_DATABASE,
        self::STEP_USER_TOKEN,
        self::STEP_MODULES,
        self::STEP_FINALIZING,
        self::STEP_COMPLETED,
    ];

    protected $table = 'tenant_provisionings';

    protected $fillable = [
        'tenant_id',
        'table',
        'table_user',
        'table_password',
        'first_user',
        'install',
    ];

    protected $casts = [
        'first_user' => 'array',
        'install' => 'string',
    ];

    public function installAtLeast(string $step): bool
    {
        $currentIndex = array_search($this->install, self::INSTALL_STEPS, true);
        $targetIndex = array_search($step, self::INSTALL_STEPS, true);

        if ($currentIndex === false || $targetIndex === false) {
            return false;
        }

        return $currentIndex >= $targetIndex;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
