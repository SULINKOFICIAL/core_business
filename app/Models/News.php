<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{

    protected $table = 'news';

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $fillable = [
        'title',
        'resume',
        'tags',
        'body',
        'category_id',
        'priority',
        'start_date',
        'end_date',
        'cta_text',
        'cta_url',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(NewsCategory::class);
    }
}
