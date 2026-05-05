<?php

namespace App\Jobs;

use App\DTOs\Payments\CycleDataDTO;
use App\DTOs\Payments\OrderDataDTO;
use App\DTOs\Payments\PaymentDataDTO;
use App\DTOs\Payments\SubscriptionDataDTO;
use App\DTOs\Payments\TransactionDataDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPlan;
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
                => $paymentProcessingService->saveSubscription($this->mapSubscriptionPayload($pagarMeDTO, 'pagarme')),
            'invoice.created',
            'charge.created',
            'invoice.paid',
            'charge.antifraud_approved',
            'charge.paid',
            'invoice.payment_failed',
            'charge.payment_failed',
                => $paymentProcessingService->savePayment($this->mapPaymentPayload($pagarMeDTO, 'pagarme')),
            default
                => true
        };
    }

    /**
     * Mapeia os dados de assinatura do evento para um payload simples e padronizado.
     */
    private function mapSubscriptionPayload(object $data, string $provider): SubscriptionDataDTO
    {
        /**
         * Retorna apenas os campos necessários para o serviço de pagamento salvar a assinatura.
         */
        return new SubscriptionDataDTO(
            provider: $provider,
            provider_subscription_id: $data->subscription->id,
            provider_card_id: $data->subscription->cardId,
            interval: $data->subscription->interval,
            payment_method: $data->subscription->method,
            currency: $data->subscription->currency,
            installments: $data->subscription->installments,
            status: $data->subscription->status,
        );
    }

    /**
     * Mapeia os dados de pagamento do evento para um payload já resolvido para persistência.
     */
    private function mapPaymentPayload(object $data, string $provider): PaymentDataDTO
    {
        /**
         * Resolve o tenant a partir do customer externo recebido no evento.
         */
        $tenant = Tenant::where('pagarme_customer_id', $data->customer->id)->firstOrFail();
        /**
         * Resolve a assinatura local vinculada ao identificador externo e ao provedor.
         */
        $subscription = Subscription::where('provider_subscription_id', $data->subscription->id)
            ->where('provider', $provider)
            ->firstOrFail();
        /**
         * Busca o último plano ativo do tenant para vincular ao pedido.
         */
        $lastPlan = TenantPlan::where('tenant_id', $tenant->id)->orderBy('id', 'desc')->first();

        /**
         * Normaliza valores monetários e campos opcionais recebidos do provider.
         */
        $amount = isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0;
        $paidAt = isset($data->charge?->paidAt) ? $data->charge->paidAt : null;
        $providerMessage = isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : null;
        $gatewayCode = isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : null;

        $orderData = new OrderDataDTO(
            status: $data->charge->status,
            provider_method: $data->invoice->method,
            provider_message: $providerMessage,
            currency: $data->charge->currency,
            total_amount: $amount,
            paid_at: $paidAt,
        );

        $transactionData = new TransactionDataDTO(
            provider_method: $data->invoice->method,
            provider_transaction_id: $data->charge->id,
            gateway_code: $gatewayCode,
            status: $data->charge->status,
            currency: $data->charge->currency,
            recurrency: $data->charge->recurrency,
            amount: $amount,
            paid_at: $paidAt,
            response: $this->requestData,
        );

        /**
         * Quando houver ciclo no evento, anexa os dados já normalizados ao payload.
         */
        $cycleData = null;
        if (isset($data->cycle)) {
            $cycleData = new CycleDataDTO(
                provider_cycle_id: $data->cycle->id,
                start_date: $data->cycle->startDate,
                end_date: $data->cycle->endDate,
                status: $data->cycle->status,
                cycle: $data->cycle->cycle,
                billing_at: $data->cycle->billingAt,
                next_billing_at: $data->cycle->nextBillingAt,
            );
        }

        /**
         * Retorna o payload pronto para o serviço de processamento salvar sem parsing adicional.
         */
        return new PaymentDataDTO(
            provider: $provider,
            tenant_id: $tenant->id,
            subscription_id: $subscription->id,
            plan_id: $lastPlan?->id,
            order: $orderData,
            transaction: $transactionData,
            cycle: $cycleData,
        );
    }
}
