<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogsApi extends Model
{
    protected $table = 'logs_apis';
    protected $fillable = [
        'api',
        'json',
        'reprocessed',
        'new_log_id',
        'status',
    ];
}
