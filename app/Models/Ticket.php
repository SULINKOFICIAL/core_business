<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $casts = [
        'requester_user' => 'array',
        'opened_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'tenant_id',
        'requester_user',
        'progress',
        'status',
        'filed_by',
        'opened_at',
        'finished_by',
        'finished_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id')->orderBy('id');
    }

    public function filedByUser()
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function finishedByUser()
    {
        return $this->belongsTo(User::class, 'finished_by');
    }
}
