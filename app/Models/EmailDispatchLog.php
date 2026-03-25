<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDispatchLog extends Model
{
    protected $table = 'email_dispatch_logs';

    /**
     * Define os campos aceitos para registrar o resultado dos envios.
     * Isso centraliza o contrato do log básico pedido para o disparo de e-mails.
     */
    protected $fillable = [
        'recipient_email',
        'recipient_name',
        'subject',
        'template',
        'status',
        'error_message',
        'payload',
        'sent_at',
    ];

    /**
     * Faz o cast do payload e da data de envio para facilitar leitura posterior.
     * O payload em array ajuda a inspecionar o conteúdo enviado no teste.
     */
    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];
}
