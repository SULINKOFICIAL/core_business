<?php

namespace App\Jobs\Middleware;

use Closure;

/**
 * Inicializa o LogApi antes de executar o job
 */
class LogApiInitializer
{
    public function handle($job, Closure $next)
    {
        /**
         * Verifica se o job tem o método initializeLogApi
         * Se sim, executa
         */
        if (method_exists($job, 'initializeLogApi')) {
            $job->initializeLogApi();
        }

        /**
         * Continua a execução do job
         */
        return $next($job);
    }
}
