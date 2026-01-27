<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleCategory extends Model
{
    protected $table = 'modules_categories';

    protected $fillable = [
        'name',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'module_category_id');
    }
}
