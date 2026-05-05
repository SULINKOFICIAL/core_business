<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\Tenant;
use App\Models\TenantPlan;
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
    public function saveSubscription(object $data, string $provider = 'pagarme'): bool
    {
        /**
         * Procura uma assinatura existente usando o identificador externo e o provedor.
         */
        $subscription = Subscription::where('provider_subscription_id', $data->subscription->id)
            ->where('provider', $provider)
            ->first();

        /**
         * Se não existir, cria uma nova assinatura com os dados do evento.
         */
        if (!$subscription) {
            $this->createSubscription($data, $provider);
            return true;
        }

        /**
         * Se já existir, apenas atualiza os dados que podem mudar ao longo do ciclo.
         */
        $this->updateSubscription($subscription, $data, $provider);

        /**
         * Retorna sucesso para o fluxo do job.
         */
        return true;
    }

    /**
     * Salva o pagamento do evento, decidindo entre criar os registros iniciais ou atualizar os já existentes.
     */
    public function savePayment(object $data, array $requestData, string $provider = 'pagarme'): bool
    {
        /**
         * Tenta localizar a transação pelo id externo e pelo provedor.
         */
        $transaction = OrderTransaction::where('provider_transaction_id', $data->charge->id)
            ->where('provider', $provider)
            ->first();

        /**
         * Quando não encontra, cria os registros iniciais do pagamento.
         */
        if (!$transaction) {
            $this->createPayment($data, $requestData, $provider);
            return true;
        }

        /**
         * Quando já existe, atualiza os dados da transação e do pedido.
         */
        $this->updatePayment($transaction, $data, $requestData, $provider);

        /**
         * Retorna sucesso para o fluxo do job.
         */
        return true;
    }

    /**
     * Cria a assinatura com os dados vindos do provedor no primeiro recebimento desse vínculo.
     */
    private function createSubscription(object $data, string $provider): Subscription
    {
        /**
         * Persiste uma nova assinatura vinculada ao identificador externo do provedor.
         */
        return Subscription::create([
            'provider'                => $provider,
            'provider_subscription_id'=> $data->subscription->id,
            'provider_card_id'        => $data->subscription->cardId,
            'interval'                => $data->subscription->interval,
            'payment_method'          => $data->subscription->method,
            'currency'                => $data->subscription->currency,
            'installments'            => $data->subscription->installments,
            'status'                  => $data->subscription->status,
        ]);
    }

    /**
     * Atualiza os dados principais da assinatura para manter o cadastro alinhado ao status atual.
     */
    private function updateSubscription(Subscription $subscription, object $data, string $provider): void
    {
        /**
         * Atualiza os campos da assinatura com o estado mais recente recebido no webhook.
         */
        $subscription->update([
            'provider'                => $provider,
            'provider_card_id'        => $data->subscription->cardId,
            'interval'                => $data->subscription->interval,
            'payment_method'          => $data->subscription->method,
            'currency'                => $data->subscription->currency,
            'installments'            => $data->subscription->installments,
            'status'                  => $data->subscription->status,
        ]);
    }

    /**
     * Cria pedido, transação e ciclo inicial (quando existir), registrando o primeiro estado do pagamento.
     */
    private function createPayment(object $data, array $requestData, string $provider): void
    {
        /**
         * Busca o tenant pelo customer da PagarMe.
         */
        $tenant = Tenant::where('pagarme_customer_id', $data->customer->id)->first();
        /**
         * Busca a assinatura correspondente ao evento.
         */
        $subscription = Subscription::where('provider_subscription_id', $data->subscription->id)
            ->where('provider', $provider)
            ->first();
        /**
         * Busca o último plano do tenant para vincular ao pedido.
         */
        $lastPackage = TenantPlan::where('tenant_id', $tenant->id)->orderBy('id', 'desc')->first();

        /**
         * Cria o pedido inicial do pagamento.
         */
        $order = Order::create([
            'tenant_id'         => $tenant->id,
            'subscription_id'   => $subscription->id,
            'plan_id'           => $lastPackage->id ?? null,
            'status'            => $data->charge->status,
            'provider'          => $provider,
            'provider_method'   => $data->invoice->method,
            'provider_message'  => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : null,
            'currency'          => $data->charge->currency,
            'total_amount'      => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
            'paid_at'           => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
        ]);

        /**
         * Cria a transação financeira vinculada ao pedido recém-criado.
         */
        OrderTransaction::create([
            'order_id'                => $order->id,
            'subscription_id'         => $subscription->id,
            'provider'                => $provider,
            'provider_method'         => $data->invoice->method,
            'provider_transaction_id' => $data->charge->id,
            'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : null,
            'status'                  => $data->charge->status,
            'currency'                => $data->charge->currency,
            'recurrency'              => $data->charge->recurrency,
            'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
            'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
            'response'                => $requestData,
        ]);

        /**
         * Se o evento não trouxe ciclo, encerra aqui.
         */
        if (!isset($data->cycle)) {
            return;
        }

        /**
         * Salva o ciclo de cobrança relacionado a essa transação.
         */
        $this->saveCycle($subscription->id, $data->cycle, $provider);
        /**
         * Sincroniza os acessos do tenant após confirmação do pagamento.
         */
        $this->syncTenantAccess($tenant, $data->cycle);
    }

    /**
     * Atualiza transação e pedido já existentes, e sincroniza o ciclo quando houver novidade no evento.
     */
    private function updatePayment(OrderTransaction $transaction, object $data, array $requestData, string $provider): void
    {
        /**
         * Atualiza os dados da transação com as informações mais recentes do webhook.
         */
        $transaction->update([
            'provider'                => $provider,
            'provider_method'         => $data->invoice->method,
            'status'                  => $data->charge->status,
            'currency'                => $data->charge->currency,
            'recurrency'              => $data->charge->recurrency,
            'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->amount,
            'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->paid_at,
            'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : $transaction->gateway_code,
            'response'                => $requestData,
        ]);

        /**
         * Atualiza o pedido associado para refletir o mesmo estado da transação.
         */
        $transaction->order->update([
            'status'                 => $data->charge->status,
            'provider'               => $provider,
            'provider_method'        => $data->invoice->method,
            'provider_message'       => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : $transaction->order->provider_message,
            'currency'               => $data->charge->currency,
            'total_amount'           => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->order->total_amount,
            'paid_at'                => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->order->paid_at,
        ]);

        /**
         * Se o ciclo não veio no evento, não há nada para atualizar nessa parte.
         */
        if (!isset($data->cycle)) {
            return;
        }

        /**
         * Salva ou atualiza o ciclo do período atual da assinatura.
         */
        $this->saveCycle($transaction->subscription_id, $data->cycle, $provider);
        /**
         * Reaplica os acessos do tenant com base no ciclo atualizado.
         */
        $this->syncTenantAccess($transaction->order->tenant, $data->cycle);
    }

    /**
     * Mantém o ciclo de cobrança atualizado, criando quando ainda não existe e atualizando quando já foi criado.
     */
    private function saveCycle(int $subscriptionId, object $cycleData, string $provider): void
    {
        /**
         * Procura um ciclo existente pelo identificador externo.
         */
        $cycle = SubscriptionCycle::where('provider_cycle_id', $cycleData->id)
            ->where('provider', $provider)
            ->first();

        /**
         * Se não existir, cria um novo ciclo completo.
         */
        if (!$cycle) {
            SubscriptionCycle::create([
                'subscription_id'  => $subscriptionId,
                'provider'         => $provider,
                'provider_cycle_id'=> $cycleData->id,
                'start_date'       => $cycleData->startDate,
                'end_date'         => $cycleData->endDate,
                'status'           => $cycleData->status,
                'cycle'            => $cycleData->cycle,
                'billing_at'       => $cycleData->billingAt,
                'next_billing_at'  => $cycleData->nextBillingAt,
            ]);
            return;
        }

        /**
         * Se já existir, atualiza apenas os dados que variam no tempo.
         */
        $cycle->update([
            'status'           => $cycleData->status,
            'billing_at'       => $cycleData->billingAt,
            'next_billing_at'  => $cycleData->nextBillingAt,
        ]);
    }

    /**
     * Reaplica os acessos do tenant após confirmação de cobrança para garantir que o plano vigente esteja refletido.
     */
    private function syncTenantAccess(Tenant $tenant, object $cycle): void
    {
        /**
         * Reaplica a configuração do plano para manter o tenant consistente após o pagamento.
         */
        $this->syncService->syncFromCurrentPlan(
            $tenant,
            source: 'order_paid',
            operatorId: null,
            reason: 'Webhook PagarMe aprovado',
            startDate: $cycle->billingAt ?? null,
            endDate: $cycle->nextBillingAt ?? null,
        );
    }
}
