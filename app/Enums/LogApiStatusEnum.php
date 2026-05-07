<?php

namespace App\Enums;

enum LogApiStatusEnum: string
{
    case RECEIVED    = 'Recebido';
    case PROCESSED   = 'Processado';
    case SENT        = 'Enviado';
    case FAILED      = 'Falhou';
    case REPROCESSED = 'Reprocessado';
    case IGNORED     = 'Ignorado';
    case DUPLICATED  = 'Duplicado';

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }
}
