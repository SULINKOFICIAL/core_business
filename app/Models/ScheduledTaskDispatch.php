<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledTaskDispatch extends Model
{
    protected $table = 'scheduled_task_dispatches';

    protected $casts = [
        'job_data' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $fillable = [
        'job_name',
        'job_data',
        'source',
        'dispatched_by',
        'total_clients',
        'success_count',
        'failure_count',
        'started_at',
        'finished_at',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ScheduledTaskDispatchItem::class, 'dispatch_id')->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }
}
