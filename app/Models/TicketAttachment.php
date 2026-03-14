<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class TicketAttachment extends Model
{
    protected $table = 'ticket_attachments';

    protected $fillable = [
        'ticket_id',
        'original_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function url(): string
    {
        if (!empty(env('AWS_URL'))) {
            return rtrim(env('AWS_URL'), '/') . '/' . ltrim($this->file_path, '/');
        }

        return 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . ltrim($this->file_path, '/');
    }
}
