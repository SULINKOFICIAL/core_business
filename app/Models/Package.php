<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'value',
        'duration_days',
        'order',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'clients_packages')
                    ->withPivot('start_date', 'end_date', 'status')
                    ->withTimestamps();
    }
}
