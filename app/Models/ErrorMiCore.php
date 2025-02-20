<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorMiCore extends Model
{
    protected $table = 'errors_micore';
    protected $fillable = [
        'client_id',
        'url',
        'ip_address',
        'message',
        'stack_trace',
    ];
}