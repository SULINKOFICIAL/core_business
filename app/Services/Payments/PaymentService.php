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
     *
     * Decide entre criar ou atualizar o pagamento com base na existência da transação.
     *
     */
    public function create(PaymentDataDTO $paymentData): void
    {
        $transaction = OrderTransaction::where('provider_transaction_id', $paymentData->transaction->provider_transaction_id)
            ->where('provider', $paymentData->provider)
            ->first();

        if (!$transaction) {
            $this->createPayment($paymentData);
            return;
        }

        $this->updatePayment($transaction, $paymentData);
    }

    /**
     *
     * Cria pedido e transação do pagamento, com ciclo apenas quando houver assinatura.
     *
     */
    private function createPayment(PaymentDataDTO $paymentData): void
    {
        $tenant = $this->tenantService->findTenant($paymentData->tenant_id);

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

        if (!$paymentData->cycle || !$paymentData->subscription_id) {
            return;
        }

        $this->saveCycle($paymentData->subscription_id, $paymentData->cycle, $paymentData->provider);
        $this->syncTenantAccess($tenant, $paymentData->cycle);
    }

    /**
     *
     * Atualiza transação e pedido existentes, sincronizando ciclo somente com assinatura vinculada.
     *
     */
    private function updatePayment(OrderTransaction $transaction, PaymentDataDTO $paymentData): void
    {
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

        if ($paymentData->subscription_id && !$transaction->subscription_id) {
            $transaction->update([
                'subscription_id' => $paymentData->subscription_id,
            ]);
        }

        $subscriptionId = $paymentData->subscription_id ?? $transaction->subscription_id;

        if (!$paymentData->cycle || !$subscriptionId) {
            return;
        }

        $this->saveCycle($subscriptionId, $paymentData->cycle, $paymentData->provider);
        $this->syncTenantAccess($transaction->order->tenant, $paymentData->cycle);
    }

    /**
     *
     * Mantém o ciclo de cobrança atualizado por provedor e ciclo externo.
     *
     */
    private function saveCycle(int $subscriptionId, CycleDataDTO $cycleData, string $provider): void
    {
        $cycle = SubscriptionCycle::where('provider_cycle_id', $cycleData->provider_cycle_id)
            ->where('provider', $provider)
            ->first();

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

        $cycle->update([
            'status'          => $cycleData->status,
            'billing_at'      => $cycleData->billing_at,
            'next_billing_at' => $cycleData->next_billing_at,
        ]);
    }

    /**
     *
     * Reaplica os acessos do tenant após confirmação de cobrança com ciclo.
     *
     */
    private function syncTenantAccess(Tenant $tenant, CycleDataDTO $cycle): void
    {
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
