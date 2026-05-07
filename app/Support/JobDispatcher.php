<?php

namespace App\Support;

class JobDispatcher
{
    /**
     * Função responsável por disparar jobs,
     * setando as propriedades do InicializerJob
     * de forma padronizada.
     *
     * @param string $jobClass Classe do job
     * @param array $payload Dados do job concreto
     * @param int|null $logApiId ID do logApi
     * @param string $queue Nome da fila
     */
    public static function dispatch(string $jobClass, array $payload = [], ?int $logApiId = null, string $queue = 'default')
    {
        // Instancia o job concreto com apenas os dados do payload
        $job = new $jobClass(...$payload);

        // Seta propriedades do InicializerJob
        $job->logApiId = $logApiId;

        // Dispara na fila
        return dispatch($job)->onQueue($queue);
    }
}
