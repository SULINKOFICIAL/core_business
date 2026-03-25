<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /**
     * Define as colunas liberadas para persistir configuracoes globais do sistema.
     * Isso mantém a escrita segura e previsível nos updates da tela administrativa.
     */
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    /**
     * Converte flags simples para tipos nativos do PHP.
     * O cast facilita o tratamento da senha criptografada no service.
     */
    protected $casts = [
        'is_encrypted' => 'boolean',
    ];
}
