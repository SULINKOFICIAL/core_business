<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledTaskDispatchItem extends Model
{
    protected $table = 'scheduled_task_dispatch_items';

    protected $casts = [
        'success' => 'boolean',
        'requested_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $fillable = [
        'dispatch_id',
        'tenant_id',
        'job_name',
        'success',
        'response_status_code',
        'response_message',
        'response_body',
        'requested_at',
        'finished_at',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(ScheduledTaskDispatch::class, 'dispatch_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
