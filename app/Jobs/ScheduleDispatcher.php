<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ScheduledTaskDispatch;
use App\Models\ScheduledTaskDispatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GuzzleService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ScheduleDispatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $guzzleService;
    protected $jobName;
    protected $jobData;

    public function __construct($jobName, $jobData = [])
    {
        $this->jobName = $jobName;
        $this->jobData = $jobData;
        $this->guzzleService = new GuzzleService();
    }

    /**
     * Dispara jobs agendados da central para todos os tenants ativos.
     * Também controla quando a central deve aguardar resposta imediata do tenant.
     * Isso mantém jobs lentos assíncronos e jobs rápidos com retorno imediato.
     */
    public function handle()
    {
        // Busca todos os tenants ativos
        $clients = Client::where('status', true)->get();

        // Verifica se as tabelas do histórico estão prontas
        $canTrack = Schema::hasTable('scheduled_task_dispatches') && Schema::hasTable('scheduled_task_dispatch_items');
        $hasItemJobName = $canTrack ? Schema::hasColumn('scheduled_task_dispatch_items', 'job_name') : false;

        // Cria lote de execução agendada
        $dispatch = null;
        $successCount = 0;
        $failureCount = 0;
        $syncFailures = [];

        if ($canTrack) {
            $dispatch = ScheduledTaskDispatch::create([
                'job_name' => $this->jobName,
                'job_data' => $this->jobData,
                'source' => 'scheduler',
                'dispatched_by' => null,
                'total_clients' => $clients->count(),
                'success_count' => 0,
                'failure_count' => 0,
                'started_at' => now(),
            ]);
        }

        /**
         * Envia o comando para cada cliente.
         */
        foreach ($clients as $client) {
            $requestedAt = now();

            // Define se o job agendado precisa executar e responder no mesmo request.
            $waitForResponse = $this->shouldWaitForResponse($this->jobName);

            // Aumenta o timeout apenas para jobs síncronos, sem penalizar o restante dos disparos.
            $response = $this->guzzleService->request(
                'post', 
                'sistema/processar-tarefa', 
                $client, 
                [
                    'job' => $this->jobName,
                    'data' => $this->jobData,
                    'wait_for_response' => $waitForResponse,
                ],
                [
                    'timeout' => $waitForResponse ? 20 : 5,
                ]
            );

            $success = (bool) ($response['success'] ?? false);
            $message = $response['message'] ?? ($success ? 'Tarefa aceita para processamento.' : 'Erro desconhecido');

            // Tenta capturar "message" da resposta JSON do cliente
            if (!empty($response['data'])) {
                $decodedResponse = json_decode($response['data'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse) && !empty($decodedResponse['message'])) {
                    $message = $decodedResponse['message'];
                }
            }

            // Registra item do lote por cliente
            if ($canTrack && $dispatch) {
                $itemData = [
                    'dispatch_id' => $dispatch->id,
                    'client_id' => $client->id,
                    'success' => $success,
                    'response_status_code' => $response['status_code'] ?? null,
                    'response_message' => $message,
                    'response_body' => $response['data'] ?? null,
                    'requested_at' => $requestedAt,
                    'finished_at' => now(),
                ];

                if ($hasItemJobName) {
                    $itemData['job_name'] = $this->jobName;
                }

                ScheduledTaskDispatchItem::create($itemData);
            }

            if ($success) {
                $successCount++;
            } else {
                $failureCount++;

                // Junta falhas síncronas para publicar um resumo único ao final do lote.
                if ($waitForResponse) {
                    $syncFailures[] = [
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'job_name' => $this->jobName,
                        'status_code' => $response['status_code'] ?? null,
                        'message' => $message,
                    ];
                }
            }

            Log::info('Disparo de job realizado para cliente', [
                'client_id' => $client->id,
                'job_name' => $this->jobName,
                'success' => $success,
            ]);

        }

        // Atualiza lote com os totais finais
        if ($canTrack && $dispatch) {
            $dispatch->update([
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'finished_at' => now(),
            ]);
        }

        $this->logSyncFailures($dispatch?->id, $syncFailures);
    }

    /**
     * Define quais jobs agendados precisam aguardar resposta do tenant.
     * Isso é usado para tarefas rápidas em que a central precisa saber o resultado na hora.
     * Hoje o refresh de token do Mercado Livre é tratado dessa forma.
     */
    private function shouldWaitForResponse(string $jobName): bool
    {
        return in_array($jobName, [
            'refresh_mercado_livre',
        ], true);
    }

    /**
     * Consolida em um único log as falhas síncronas do disparo agendado.
     * Isso reduz ruído e cria um ponto simples para futura geração de alertas.
     * O método não registra nada quando todas as execuções tiverem sucesso.
     */
    private function logSyncFailures(?int $dispatchId, array $syncFailures): void
    {
        // Sai cedo quando não houver falhas para resumir.
        if (empty($syncFailures)) {
            return;
        }

        // Publica o resumo do lote com os clientes que retornaram erro imediato.
        Log::warning('Falhas detectadas em jobs síncronos agendados', [
            'dispatch_id' => $dispatchId,
            'job_name' => $this->jobName,
            'failures_count' => count($syncFailures),
            'failures' => $syncFailures,
        ]);
    }

}
