<?php

namespace App\Services\Payments;

use App\DTOs\Payments\{
    CycleDataDTO,
    PaymentDataDTO
};
use App\Models\{
    Order,
    OrderTransaction,
    SubscriptionCycle,
    Tenant,
};
use App\Services\{
    TenantConfigurationSyncService,
    TenantService
};

class PaymentService
{
    public function __construct(
        private TenantConfigurationSyncService $syncService,
        private TenantService $tenantService
    ) {
    }

    /**
     * Decide entre criar ou atualizar o pagamento com base na existência da transação.
     */
    public function create(PaymentDataDTO $paymentData): void
    {
        // Busca a transação pelo identificador do provider
        $transaction = OrderTransaction::where('provider_transaction_id', $paymentData->transaction->provider_transaction_id)
            ->where('provider', $paymentData->provider)
            ->first();

        // Cria novo pagamento quando não existe transação anterior
        if (!$transaction) {
            $this->createPayment($paymentData);
            return;
        }

        // Atualiza o pagamento existente
        $this->updatePayment($transaction, $paymentData);
    }

    /**
     * Cria pedido e transação do pagamento, com ciclo apenas quando houver assinatura.
     */
    private function createPayment(PaymentDataDTO $paymentData): void
    {
        // Obtem o tenant do pagamento
        $tenant = $this->tenantService->findTenant($paymentData->tenant_id);

        // Cria o pedido com os dados retornados pelo provider
        $order = Order::create([
            'tenant_id'        => $paymentData->tenant_id,
            'subscription_id'  => $paymentData->subscription_id,
            'plan_id'          => $paymentData->plan_id,
            'status'           => $paymentData->order->status,
            'provider'         => $paymentData->provider,
            'provider_method'  => $paymentData->order->provider_method,
            'provider_message' => $paymentData->order->provider_message,
            'currency'         => $paymentData->order->currency,
            'total_amount'     => $paymentData->order->total_amount,
            'paid_at'          => $paymentData->order->paid_at,
        ]);

        // Cria a transação vinculada ao pedido
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

        // Encerra o fluxo quando não há ciclo ou assinatura
        if (!$paymentData->cycle || !$paymentData->subscription_id) {
            return;
        }

        // Persiste o ciclo de cobrança
        $this->saveCycle($paymentData->subscription_id, $paymentData->cycle, $paymentData->provider);

        // Reaplica os acessos do tenant
        $this->syncTenantAccess($tenant, $paymentData->cycle);
    }

    /**
     * Atualiza transação e pedido existentes, sincronizando ciclo somente com assinatura vinculada.
     */
    private function updatePayment(OrderTransaction $transaction, PaymentDataDTO $paymentData): void
    {
        // Atualiza a transação com os dados do provider
        $transaction->update([
            'provider'        => $paymentData->provider,
            'provider_method' => $paymentData->transaction->provider_method,
            'status'          => $paymentData->transaction->status,
            'currency'        => $paymentData->transaction->currency,
            'recurrency'      => $paymentData->transaction->recurrency,
            'amount'          => $paymentData->transaction->amount,
            'paid_at'         => $paymentData->transaction->paid_at ?? $transaction->paid_at,
            'gateway_code'    => $paymentData->transaction->gateway_code ?? $transaction->gateway_code,
            'response'        => $paymentData->transaction->response,
        ]);

        // Atualiza o pedido vinculado à transação
        $transaction->order->update([
            'subscription_id'  => $paymentData->subscription_id ?? $transaction->order->subscription_id,
            'plan_id'          => $paymentData->plan_id ?? $transaction->order->plan_id,
            'status'           => $paymentData->order->status,
            'provider'         => $paymentData->provider,
            'provider_method'  => $paymentData->order->provider_method,
            'provider_message' => $paymentData->order->provider_message ?? $transaction->order->provider_message,
            'currency'         => $paymentData->order->currency,
            'total_amount'     => $paymentData->order->total_amount,
            'paid_at'          => $paymentData->order->paid_at ?? $transaction->order->paid_at,
        ]);

        // Vincula a assinatura à transação quando informada e ainda não existe
        if ($paymentData->subscription_id && !$transaction->subscription_id) {
            $transaction->update([
                'subscription_id' => $paymentData->subscription_id,
            ]);
        }

        // Obtem o id da assinatura priorizando o payload atual
        $subscriptionId = $paymentData->subscription_id ?? $transaction->subscription_id;

        // Encerra o fluxo quando não há ciclo ou assinatura
        if (!$paymentData->cycle || !$subscriptionId) {
            return;
        }

        // Persiste o ciclo de cobrança
        $this->saveCycle($subscriptionId, $paymentData->cycle, $paymentData->provider);

        // Reaplica os acessos do tenant
        $this->syncTenantAccess($transaction->order->tenant, $paymentData->cycle);
    }

    /**
     * Mantém o ciclo de cobrança atualizado por provedor e ciclo externo.
     */
    private function saveCycle(int $subscriptionId, CycleDataDTO $cycleData, string $provider): void
    {
        // Busca ciclo já existente pelo identificador do provider
        $cycle = SubscriptionCycle::where('provider_cycle_id', $cycleData->provider_cycle_id)
            ->where('provider', $provider)
            ->first();

        // Cria novo ciclo quando não existe
        if (!$cycle) {
            SubscriptionCycle::create([
                'subscription_id'   => $subscriptionId,
                'provider'          => $provider,
                'provider_cycle_id' => $cycleData->provider_cycle_id,
                'start_date'        => $cycleData->start_date,
                'end_date'          => $cycleData->end_date,
                'status'            => $cycleData->status,
                'cycle'             => $cycleData->cycle,
                'billing_at'        => $cycleData->billing_at,
                'next_billing_at'   => $cycleData->next_billing_at,
            ]);
            return;
        }

        // Atualiza ciclo existente apenas com status e datas de cobrança
        $cycle->update([
            'status'          => $cycleData->status,
            'billing_at'      => $cycleData->billing_at,
            'next_billing_at' => $cycleData->next_billing_at,
        ]);
    }

    /**
     * Reaplica os acessos do tenant após confirmação de cobrança com ciclo.
     */
    private function syncTenantAccess(Tenant $tenant, CycleDataDTO $cycle): void
    {
        // Sincroniza os acessos do tenant a partir do plano atual
        $this->syncService->syncFromCurrentPlan(
            $tenant,
            source: 'order_paid',
            operatorId: null,
            reason: 'Webhook aprovado',
            startDate: $cycle->billing_at,
            endDate: $cycle->next_billing_at,
        );
    }
}
