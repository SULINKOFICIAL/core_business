<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSuggestion extends Model
{
    protected $table = 'integration_suggestions';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'client_id',
        'progress',
        'status',
        'filed_by',
        'finished_by',
        'finished_at',
    ];
}
