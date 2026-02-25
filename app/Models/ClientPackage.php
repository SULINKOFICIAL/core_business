<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ClientPackage extends Model
{
    protected $table = 'clients_packages';

    protected $fillable = [
        'client_id',
        'name',
        'value',
        'progress',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClientPackageItem::class, 'package_id', 'id');
    }

    public function modules(): HasManyThrough
    {
        return $this->hasManyThrough(
            Module::class,
            ClientPackageItem::class,
            'package_id',
            'id',
            'id',
            'item_id',
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'package_id', 'id');
    }
}
