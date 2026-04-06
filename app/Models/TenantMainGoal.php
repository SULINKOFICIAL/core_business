<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMainGoal extends Model
{
    protected $table = 'tenants_main_goals';

    protected $fillable = [
        'client_id',
        'goal',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'client_id', 'id');
    }
}
