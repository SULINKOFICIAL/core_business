<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Services\PagarMeResponseService;
use App\Services\Payments\PaymentProcessingService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PagarMeDispatchRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    protected $logApiId;
    protected $logApi;

    // Número máximo de tentativas
    public $tries = 1;

    // Tempo de espera entre as tentativas (segundos)
    public $backoff = 10;

    // Tempo máximo (segundos) que o Job pode executar
    public function timeout()
    {
        return 60;
    }

    public function __construct(array $requestData, $logApiId = null)
    {
        $this->requestData = $requestData;
        $this->logApiId = $logApiId;

        // Busca o logApi
        $this->logApi = LogsApi::find($this->logApiId);
    }

    public function handle(
        PagarMeResponseService $pagarMeResponseService,
        PaymentProcessingService $paymentProcessingService
    )
    {

        /**
         * DTO Formatado da PagarMe
         */
        $pagarMeDTO = $pagarMeResponseService->process($this->requestData);

        /**
         * Se for Webhook com lógica de duplicidade
         * Retorna
         */
        if (!$pagarMeDTO) {
            return true;
        }

        return match ($pagarMeDTO->type) {
            'subscription.created',
            'subscription.updated',
            => $paymentProcessingService->saveSubscription($pagarMeDTO, 'pagarme'),
            'invoice.created',
            'charge.created',
            'invoice.paid',
            'charge.antifraud_approved',
            'charge.paid',
            'invoice.payment_failed',
            'charge.payment_failed',
            => $paymentProcessingService->savePayment($pagarMeDTO, $this->requestData, 'pagarme'),
            default => true
        };
    }
}
