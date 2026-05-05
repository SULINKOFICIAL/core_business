<?php

namespace App\Services\Payments;

use App\DTOs\Payments\CycleDataDTO;
use App\DTOs\Payments\PaymentDataDTO;
use App\DTOs\Payments\SubscriptionDataDTO;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\Tenant;
use App\Services\TenantConfigurationSyncService;

class PaymentProcessingService
{
    public function __construct(
        private TenantConfigurationSyncService $syncService
    ) {
    }

    /**
     * Salva a assinatura com base no evento recebido, criando quando é nova e atualizando quando já existe.
     */
    public function saveSubscription(SubscriptionDataDTO $subscriptionData): bool
    {
        $subscription = Subscription::where('provider_subscription_id', $subscriptionData->provider_subscription_id)
            ->where('provider', $subscriptionData->provider)
            ->first();

        /**
         * Se não existir, cria uma nova assinatura com os dados do evento.
         */
        if (!$subscription) {
            $this->createSubscription($subscriptionData);
            return true;
        }

        /**
         * Se já existir, apenas atualiza os dados que podem mudar ao longo do ciclo.
         */
        $this->updateSubscription($subscription, $subscriptionData);

        /**
         * Retorna sucesso para o fluxo do job.
         */
        return true;
    }

    /**
     * Salva o pagamento do evento, decidindo entre criar os registros iniciais ou atualizar os já existentes.
     */
    public function savePayment(PaymentDataDTO $paymentData): bool
    {
        $transaction = OrderTransaction::where('provider_transaction_id', $paymentData->transaction->provider_transaction_id)
            ->where('provider', $paymentData->provider)
            ->first();

        /**
         * Quando não encontra, cria os registros iniciais do pagamento.
         */
        if (!$transaction) {
            $this->createPayment($paymentData);
            return true;
        }

        /**
         * Quando já existe, atualiza os dados da transação e do pedido.
         */
        $this->updatePayment($transaction, $paymentData);

        /**
         * Retorna sucesso para o fluxo do job.
         */
        return true;
    }

    /**
     * Cria a assinatura com os dados vindos do provedor no primeiro recebimento desse vínculo.
     */
    private function createSubscription(SubscriptionDataDTO $subscriptionData): Subscription
    {
        return Subscription::create([
            'provider'                 => $subscriptionData->provider,
            'provider_subscription_id' => $subscriptionData->provider_subscription_id,
            'provider_card_id'         => $subscriptionData->provider_card_id,
            'interval'                 => $subscriptionData->interval,
            'payment_method'           => $subscriptionData->payment_method,
            'currency'                 => $subscriptionData->currency,
            'installments'             => $subscriptionData->installments,
            'status'                   => $subscriptionData->status,
        ]);
    }

    /**
     * Atualiza os dados principais da assinatura para manter o cadastro alinhado ao status atual.
     */
    private function updateSubscription(Subscription $subscription, SubscriptionDataDTO $subscriptionData): void
    {
        $subscription->update([
            'provider'                 => $subscriptionData->provider,
            'provider_card_id'         => $subscriptionData->provider_card_id,
            'interval'                 => $subscriptionData->interval,
            'payment_method'           => $subscriptionData->payment_method,
            'currency'                 => $subscriptionData->currency,
            'installments'             => $subscriptionData->installments,
            'status'                   => $subscriptionData->status,
        ]);
    }

    /**
     * Cria pedido, transação e ciclo inicial (quando existir), registrando o primeiro estado do pagamento.
     */
    private function createPayment(PaymentDataDTO $paymentData): void
    {
        $tenant = Tenant::findOrFail($paymentData->tenant_id);

        $order = Order::create([
            'tenant_id'         => $paymentData->tenant_id,
            'subscription_id'   => $paymentData->subscription_id,
            'plan_id'           => $paymentData->plan_id,
            'status'            => $paymentData->order->status,
            'provider'          => $paymentData->provider,
            'provider_method'   => $paymentData->order->provider_method,
            'provider_message'  => $paymentData->order->provider_message,
            'currency'          => $paymentData->order->currency,
            'total_amount'      => $paymentData->order->total_amount,
            'paid_at'           => $paymentData->order->paid_at,
        ]);

        OrderTransaction::create([
            'order_id'                => $order->id,
            'subscription_id'         => $paymentData->subscription_id,
            'provider'                => $paymentData->provider,
            'provider_method'         => $paymentData->transaction->provider_method,
            'provider_transaction_id' => $paymentData->transaction->provider_transaction_id,
            'gateway_code'            => $paymentData->transaction->gateway_code,
            'status'                  => $paymentData->transaction->status,
            'currency'                => $paymentData->transaction->currency,
            'recurrency'              => $paymentData->transaction->recurrency,
            'amount'                  => $paymentData->transaction->amount,
            'paid_at'                 => $paymentData->transaction->paid_at,
            'response'                => $paymentData->transaction->response,
        ]);

        if (!$paymentData->cycle) {
            return;
        }

        $this->saveCycle($paymentData->subscription_id, $paymentData->cycle, $paymentData->provider);
        $this->syncTenantAccess($tenant, $paymentData->cycle);
    }

    /**
     * Atualiza transação e pedido já existentes, e sincroniza o ciclo quando houver novidade no evento.
     */
    private function updatePayment(OrderTransaction $transaction, PaymentDataDTO $paymentData): void
    {
        $transaction->update([
            'provider'                => $paymentData->provider,
            'provider_method'         => $paymentData->transaction->provider_method,
            'status'                  => $paymentData->transaction->status,
            'currency'                => $paymentData->transaction->currency,
            'recurrency'              => $paymentData->transaction->recurrency,
            'amount'                  => $paymentData->transaction->amount,
            'paid_at'                 => $paymentData->transaction->paid_at ?? $transaction->paid_at,
            'gateway_code'            => $paymentData->transaction->gateway_code ?? $transaction->gateway_code,
            'response'                => $paymentData->transaction->response,
        ]);

        $transaction->order->update([
            'status'                 => $paymentData->order->status,
            'provider'               => $paymentData->provider,
            'provider_method'        => $paymentData->order->provider_method,
            'provider_message'       => $paymentData->order->provider_message ?? $transaction->order->provider_message,
            'currency'               => $paymentData->order->currency,
            'total_amount'           => $paymentData->order->total_amount,
            'paid_at'                => $paymentData->order->paid_at ?? $transaction->order->paid_at,
        ]);

        if (!$paymentData->cycle) {
            return;
        }

        $this->saveCycle($transaction->subscription_id, $paymentData->cycle, $paymentData->provider);
        $this->syncTenantAccess($transaction->order->tenant, $paymentData->cycle);
    }

    /**
     * Mantém o ciclo de cobrança atualizado, criando quando ainda não existe e atualizando quando já foi criado.
     */
    private function saveCycle(int $subscriptionId, CycleDataDTO $cycleData, string $provider): void
    {
        $cycle = SubscriptionCycle::where('provider_cycle_id', $cycleData->provider_cycle_id)
            ->where('provider', $provider)
            ->first();

        if (!$cycle) {
            SubscriptionCycle::create([
                'subscription_id'  => $subscriptionId,
                'provider'         => $provider,
                'provider_cycle_id'=> $cycleData->provider_cycle_id,
                'start_date'       => $cycleData->start_date,
                'end_date'         => $cycleData->end_date,
                'status'           => $cycleData->status,
                'cycle'            => $cycleData->cycle,
                'billing_at'       => $cycleData->billing_at,
                'next_billing_at'  => $cycleData->next_billing_at,
            ]);
            return;
        }

        $cycle->update([
            'status'           => $cycleData->status,
            'billing_at'       => $cycleData->billing_at,
            'next_billing_at'  => $cycleData->next_billing_at,
        ]);
    }

    /**
     * Reaplica os acessos do tenant após confirmação de cobrança para garantir que o plano vigente esteja refletido.
     */
    private function syncTenantAccess(Tenant $tenant, CycleDataDTO $cycle): void
    {
        /**
         * Reaplica a configuração do plano para manter o tenant consistente após o pagamento.
         */
        $this->syncService->syncFromCurrentPlan(
            $tenant,
            source: 'order_paid',
            operatorId: null,
            reason: 'Webhook PagarMe aprovado',
            startDate: $cycle->billing_at,
            endDate: $cycle->next_billing_at,
        );
    }
}
