<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\TenantPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\OrderTransaction;
use App\Models\SubscriptionCycle;
use App\Services\TenantConfigurationSyncService;
use App\Services\PagarMeResponseService;
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
        TenantConfigurationSyncService $syncService
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
            => $this->subscriptionCreatedOrUpdated($pagarMeDTO),
            'invoice.created',
            'charge.created',
            'invoice.paid',
            'charge.antifraud_approved',
            'charge.paid',
            'invoice.payment_failed',
            'charge.payment_failed',
            => $this->paymentCreatedOrUpdated($pagarMeDTO, $syncService),
            default => true
        };
    }

    /**
     * Função responsável por verificar
     * Se existe uma assinatura criada
     * Se não existir cria uma
     */
    public function subscriptionCreatedOrUpdated($data)
    {
        // Obtem a assinatura a partir do id
        $subscription = Subscription::where('pagarme_subscription_id', $data->subscription->id)->first();

        // Se existir uma assinatura
        if (!$subscription) {

            // Cria a assinatura
            $subscription = Subscription::create([
                'pagarme_subscription_id' => $data->subscription->id,
                'pagarme_card_id'         => $data->subscription->cardId,
                'interval'                => $data->subscription->interval,
                'payment_method'          => $data->subscription->method,
                'currency'                => $data->subscription->currency,
                'installments'            => $data->subscription->installments,
                'status'                  => $data->subscription->status,
            ]);
        } else {

            // Atualiza a assinatura
            $subscription->update([
                'pagarme_subscription_id' => $data->subscription->id,
                'pagarme_card_id'         => $data->subscription->cardId,
                'interval'                => $data->subscription->interval,
                'payment_method'          => $data->subscription->method,
                'currency'                => $data->subscription->currency,
                'installments'            => $data->subscription->installments,
                'status'                  => $data->subscription->status,
            ]);
        }

        return true;
    }

    public function paymentCreatedOrUpdated($data, TenantConfigurationSyncService $syncService)
    {
        // Obtem a transação do cliente
        $transaction = OrderTransaction::where('provider_transaction_id', $data->charge->id)
            ->where('provider', 'pagarme')
            ->first();

        // Verifica se existe uma transação
        if (!$transaction) {

            // Obtem o cliente
            $tenant = Tenant::where('pagarme_customer_id', $data->customer->id)->first();

            // Obtem a assinatura
            $subscription = Subscription::where('pagarme_subscription_id', $data->subscription->id)->first();

            // Obtem o ultimo pacote do cliente
            $lastPackage = TenantPlan::where('tenant_id', $tenant->id)->orderBy('id', 'desc')->first();

            // Cria um pedido
            $order = Order::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription->id,
                'plan_id'         => $lastPackage->id ?? null,
                'status'          => $data->charge->status,
                'provider'        => 'pagarme',
                'provider_method' => $data->invoice->method,
                'currency'        => $data->charge->currency,
                'provider_message' => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : null,
                'total_amount'    => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
                'paid_at'         => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
            ]);

            // Cria a transação
            $transaction = OrderTransaction::create([
                'order_id'                => $order->id,
                'subscription_id'         => $subscription->id,
                'provider'                => 'pagarme',
                'provider_method'         => $data->invoice->method,
                'provider_transaction_id' => $data->charge->id,
                'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : null,
                'status'                  => $data->charge->status,
                'currency'                => $data->charge->currency,
                'recurrency'              => $data->charge->recurrency,
                'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
                'response'                => $this->requestData,
            ]);

            // Verifica se existe um ciclo
            if (isset($data->cycle)) {
                
                // Cria o ciclo
                SubscriptionCycle::create([
                    'subscription_id'  => $subscription->id,
                    'pagarme_cycle_id' => $data->cycle->id,
                    'start_date'       => $data->cycle->startDate,
                    'end_date'         => $data->cycle->endDate,
                    'status'           => $data->cycle->status,
                    'cycle'            => $data->cycle->cycle,
                    'billing_at'       => $data->cycle->billingAt,
                    'next_billing_at'  => $data->cycle->nextBillingAt,
                ]);
                
                /**
                 * Parte responsável por liberar o MiCore
                 */
                $this->releaseModule($tenant, $lastPackage, $data->cycle);

            }

        } else {

            // Atualiza a transação
            $transaction->update([
                'status'                  => $data->charge->status,
                'provider'                => 'pagarme',
                'provider_method'         => $data->invoice->method,
                'currency'                => $data->charge->currency,
                'recurrency'              => $data->charge->recurrency,
                'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->amount,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->paid_at,
                'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : $transaction->gateway_code,
                'response'                => $this->requestData,
            ]);

            // Atualiza o pedido
            $transaction->order->update([
                'status'                  => $data->charge->status,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->order->paid_at,
                'total_amount'            => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->order->total_amount,
                'provider'                => 'pagarme',
                'provider_method'         => $data->invoice->method,
                'provider_message'        => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : $transaction->order->provider_message,
                'currency'                => $data->charge->currency,
            ]);

            // Verifica se veio o ciclo
            if (isset($data->cycle)) {

                // Verifica se existe um ciclo
                $cycle = SubscriptionCycle::where('pagarme_cycle_id', $data->cycle->id)->first();

                // Se existir um ciclo
                if ($cycle) {

                    // Atualiza o ciclo
                    $cycle->update([
                        'status'           => $data->cycle->status,
                        'billing_at'       => $data->cycle->billingAt,
                        'next_billing_at'  => $data->cycle->nextBillingAt,
                    ]);

                } else {

                    // Cria o ciclo
                    SubscriptionCycle::create([
                        'subscription_id'  => $transaction->subscription_id,
                        'pagarme_cycle_id' => $data->cycle->id,
                        'start_date'       => $data->cycle->startDate,
                        'end_date'         => $data->cycle->endDate,
                        'status'           => $data->cycle->status,
                        'cycle'            => $data->cycle->cycle,
                        'billing_at'       => $data->cycle->billingAt,
                        'next_billing_at'  => $data->cycle->nextBillingAt,
                    ]);

                    /**
                     * Parte responsável por liberar o MiCore
                     */
                    $this->releaseModule($transaction->order->tenant, $transaction->order->plan, $data->cycle, $syncService);
                }

            }

        }

        return true;
    }

    /**
     * Libera os módulos para o cliente
     */
    private function releaseModule($tenant, $plan, $cycle, TenantConfigurationSyncService $syncService)
    {
        /**
         * Webhook de cobrança aprovada reaplica a configuração consolidada
         * para garantir consistência do tenant com o plano local.
         */
        $syncService->syncFromCurrentPlan(
            $tenant,
            source: 'order_paid',
            operatorId: null,
            reason: 'Webhook PagarMe aprovado',
            startDate: $cycle->billingAt ?? null,
            endDate: $cycle->nextBillingAt ?? null,
        );
    }
}
