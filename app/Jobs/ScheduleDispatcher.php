<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\ScheduledTaskDispatch;
use App\Models\ScheduledTaskDispatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GuzzleService;
use Illuminate\Support\Facades\Log;

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
     * A resposta do tenant indica apenas se o disparo foi aceito para processamento.
     */
    public function handle()
    {
        // Busca todos os tenants ativos
        $clients = Tenant::where('status', true)->get();

        // Cria lote de execução agendada
        $successCount = 0;
        $failureCount = 0;

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

        /**
         * Envia o comando para cada cliente.
         */
        foreach ($clients as $client) {
            $requestedAt = now();

            // O tenant sempre responde apenas com o aceite do disparo.
            $response = $this->guzzleService->request(
                'post', 
                'sistema/processar-tarefa', 
                $client, 
                [
                    'job' => $this->jobName,
                    'data' => $this->jobData,
                ],
                [
                    'timeout' => 5,
                ]
            );

            $success = (bool) ($response['success'] ?? false);
            $message = $response['message'] ?? ($success ? 'Tarefa aceita para processamento.' : 'Falha ao aceitar tarefa para processamento.');

            // Tenta capturar "message" da resposta JSON do cliente
            if (!empty($response['data'])) {
                $decodedResponse = json_decode($response['data'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse) && !empty($decodedResponse['message'])) {
                    $message = $decodedResponse['message'];
                }
            }

            // Registra item do lote por cliente
            ScheduledTaskDispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'client_id' => $client->id,
                'job_name' => $this->jobName,
                'success' => $success,
                'response_status_code' => $response['status_code'] ?? null,
                'response_message' => $message,
                'response_body' => $response['data'] ?? null,
                'requested_at' => $requestedAt,
                'finished_at' => now(),
            ]);

            if ($success) {
                $successCount++;
            } else {
                $failureCount++;
            }

            Log::info('Disparo de job realizado para cliente', [
                'client_id' => $client->id,
                'job_name' => $this->jobName,
                'success' => $success,
            ]);

        }

        // Atualiza lote com os totais finais
        $dispatch->update([
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'finished_at' => now(),
        ]);
    }
}
