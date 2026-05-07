<?php

namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use App\Enums\LogApiStatusEnum;

/**
 * Garante que jobs com o mesmo payload não sejam executados mais de uma vez.
 */
class IdempotencyVerification
{
    public function handle($job, Closure $next)
    {
        /**
         * Verifica se o job tem o logApi
         * Se não tiver, não faz nada
         */
        if (!$job->logApi || env('APP_ENV') == 'local') {
            return $next($job);
        }

        // Cria uma chave de idempotência
        $idempotentKey = 'job:' . md5($job->logApi->json);

        // Verifica se ja existe cache com essa chave
        if (Cache::has($idempotentKey)) {

            // Marca o log como duplicado
            $job->logApi->status = LogApiStatusEnum::DUPLICATED->value;
            $job->logApi->save();

            // Deleta o job
            $job->delete();

            // Encerra a execução do job
            return;
        }

        /**
         * Captura o resultado antes de gravar o cache.
         * Dessa forma, se o job falhar e lançar uma exceção,
         * o cache não é gravado e o job pode ser reprocessado.
         */
        $result = $next($job);

        // Grava o cache com a chave de idempotência
        Cache::put($idempotentKey, true, 60);

        // Retorna o resultado do job
        return $result;
    }
}
