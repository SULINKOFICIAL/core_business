<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsRead extends Model
{
    //
    protected $table = 'news_reads';

    protected $fillable = [
        'news_id',
        'tenant_id',
        'client_user_id',
        'viewed_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
