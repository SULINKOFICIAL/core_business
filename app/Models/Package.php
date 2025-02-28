<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'packages_modules', 'package_id', 'module_id');
    }

}
