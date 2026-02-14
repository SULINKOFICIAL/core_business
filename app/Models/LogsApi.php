<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LogsApi extends Model
{
    protected $table = 'logs_apis';

    protected $fillable = [
        'api',
        'client_id',
        'json',
        'reprocessed',
        'new_log_id',
        'status',
        'dispatched_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
