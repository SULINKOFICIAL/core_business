<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ScheduledTaskDispatch;
use App\Models\ScheduledTaskDispatchItem;
use App\Services\EmailService;
use App\Services\MailSettingsService;
use App\Services\MetaWhatsAppTemplateService;
use App\Services\WhatsAppSettingsService;
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
     * Também controla quando a central deve aguardar resposta imediata do tenant.
     * Isso mantém jobs lentos assíncronos e jobs rápidos com retorno imediato.
     */
    public function handle(
        EmailService $emailService,
        MailSettingsService $mailSettingsService,
        MetaWhatsAppTemplateService $metaWhatsAppTemplateService,
        WhatsAppSettingsService $whatsAppSettingsService,
    )
    {
        // Busca todos os tenants ativos
        $clients = Client::where('status', true)->get();

        // Cria lote de execução agendada
        $successCount = 0;
        $failureCount = 0;
        $syncFailures = [];

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
        $dispatch->update([
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'finished_at' => now(),
        ]);

        $this->logSyncFailures(
            $dispatch->id,
            $syncFailures,
            $emailService,
            $mailSettingsService,
            $metaWhatsAppTemplateService,
            $whatsAppSettingsService,
        );
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
     * Também dispara alerta por e-mail e template WhatsApp para os destinatários configurados.
     * O método não executa nada quando todas as execuções tiverem sucesso.
     */
    private function logSyncFailures(
        ?int $dispatchId,
        array $syncFailures,
        EmailService $emailService,
        MailSettingsService $mailSettingsService,
        MetaWhatsAppTemplateService $metaWhatsAppTemplateService,
        WhatsAppSettingsService $whatsAppSettingsService,
    ): void
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

        $this->sendSyncFailureEmails($dispatchId, $syncFailures, $emailService, $mailSettingsService);
        $this->sendSyncFailureWhatsAppAlerts(
            $dispatchId,
            $syncFailures,
            $metaWhatsAppTemplateService,
            $whatsAppSettingsService,
        );
    }

    /**
     * Monta o corpo do e-mail com um resumo legível das falhas do lote.
     * Isso facilita identificar rapidamente quais tenants falharam e por qual motivo.
     */
    private function buildSyncFailureEmailBody(?int $dispatchId, array $syncFailures): string
    {
        $lines = [
            "Problemas foram detectados no dispatcher agendado da tarefa {$this->jobName}.",
            '',
            'Resumo do lote:',
            "Dispatch ID: " . ($dispatchId ?? 'N/A'),
            'Quantidade de falhas: ' . count($syncFailures),
            '',
            'Falhas identificadas:',
        ];

        foreach ($syncFailures as $index => $failure) {
            // Mantém cada falha em uma linha simples para leitura rápida no e-mail.
            $lines[] = sprintf(
                '%d. Cliente: %s | ID: %s | Status: %s | Mensagem: %s',
                $index + 1,
                $failure['client_name'] ?? 'N/A',
                $failure['client_id'] ?? 'N/A',
                $failure['status_code'] ?? 'N/A',
                $failure['message'] ?? 'Erro desconhecido',
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Dispara os alertas por e-mail para os destinatários configurados no SMTP.
     * Isso reaproveita a configuração administrativa sem acoplar o job à interface.
     */
    private function sendSyncFailureEmails(
        ?int $dispatchId,
        array $syncFailures,
        EmailService $emailService,
        MailSettingsService $mailSettingsService,
    ): void
    {
        $notificationEmails = $mailSettingsService->getNotificationEmails();

        // Sai cedo quando não houver destinatários configurados para o alerta.
        if (empty($notificationEmails)) {
            return;
        }

        $emailResult = $emailService->sendMany(
            $notificationEmails,
            "Falhas no dispatcher agendado: {$this->jobName}",
            [
                'message_body' => $this->buildSyncFailureEmailBody($dispatchId, $syncFailures),
                'cta_label' => 'Acessar MiCore',
                'cta_url' => config('app.url'),
            ]
        );

        // Registra o resultado do alerta para facilitar rastreabilidade operacional.
        Log::info('Alerta de falhas síncronas enviado por e-mail', [
            'dispatch_id' => $dispatchId,
            'job_name' => $this->jobName,
            'recipients_count' => count($notificationEmails),
            'success' => $emailResult['success'] ?? false,
            'success_count' => $emailResult['success_count'] ?? 0,
            'error_count' => $emailResult['error_count'] ?? 0,
        ]);
    }

    /**
     * Dispara o template WhatsApp configurado para os telefones de notificação.
     * Isso replica o alerta crítico também no canal operacional mais imediato.
     */
    private function sendSyncFailureWhatsAppAlerts(
        ?int $dispatchId,
        array $syncFailures,
        MetaWhatsAppTemplateService $metaWhatsAppTemplateService,
        WhatsAppSettingsService $whatsAppSettingsService,
    ): void
    {
        $notificationPhones = $whatsAppSettingsService->getNotificationPhones();
        $whatsAppSettings = $whatsAppSettingsService->getSettings();

        // Sai cedo quando não houver telefones ou configuração mínima da Meta.
        if (
            empty($notificationPhones)
            || empty($whatsAppSettings['owner_account_id'])
            || empty($whatsAppSettings['access_token'])
            || empty($whatsAppSettings['template_name'])
        ) {
            return;
        }

        $phoneNumberId = $metaWhatsAppTemplateService->getPhoneNumberIdFromOwnerAccount(
            $whatsAppSettings['owner_account_id'],
            $whatsAppSettings['access_token'],
        );

        // Sai cedo quando não for possível resolver o emissor na conta dona.
        if (! $phoneNumberId) {
            Log::warning('Falha ao resolver phone_number_id para alerta de dispatcher', [
                'dispatch_id' => $dispatchId,
                'job_name' => $this->jobName,
                'owner_account_id' => $whatsAppSettings['owner_account_id'],
            ]);

            return;
        }

        $results = [];

        foreach ($notificationPhones as $notificationPhone) {
            // Envia o template usando um resumo curto nas variáveis do modelo configurado.
            $results[] = $metaWhatsAppTemplateService->sendSystemProblemAlert(
                $phoneNumberId,
                $whatsAppSettings['access_token'],
                $notificationPhone,
                $whatsAppSettings['template_name'],
                $whatsAppSettings['template_language'],
                $this->jobName,
                $this->buildSyncFailureWhatsAppDescription($dispatchId, $syncFailures),
                now()->format('d/m/Y H:i'),
            );
        }

        $successCount = collect($results)
            ->filter(fn (array $result) => ($result['status'] ?? 500) < 400)
            ->count();

        Log::info('Alerta de falhas síncronas enviado por WhatsApp', [
            'dispatch_id' => $dispatchId,
            'job_name' => $this->jobName,
            'recipients_count' => count($notificationPhones),
            'success_count' => $successCount,
            'error_count' => count($results) - $successCount,
        ]);
    }

    /**
     * Monta uma descrição curta para caber no template WhatsApp de alerta.
     * O texto prioriza quantidade de falhas e primeiros clientes impactados.
     */
    private function buildSyncFailureWhatsAppDescription(?int $dispatchId, array $syncFailures): string
    {
        $affectedClients = collect($syncFailures)
            ->pluck('client_name')
            ->filter()
            ->take(3)
            ->implode(', ');

        $description = sprintf(
            'Dispatch %s com %d falha(s). Clientes: %s.',
            $dispatchId ?? 'N/A',
            count($syncFailures),
            $affectedClients ?: 'nao identificados',
        );

        // Limita o texto para reduzir risco de extrapolar tamanho prático do template.
        return mb_strimwidth($description, 0, 250, '...');
    }
}
