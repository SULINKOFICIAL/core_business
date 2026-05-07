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
    ) {}

    /**
     * Decide entre criar ou atualizar o pagamento com base na existência da transação.
     */
    public function create(PaymentDataDTO $paymentData): void
    {
        // Verifica se a transação já existe
        $transaction = OrderTransaction::where('provider_transaction_id', $paymentData->transaction->provider_transaction_id)
            ->where('provider', $paymentData->provider)
            ->first();

        if (!$transaction) {
            $this->createPayment($paymentData);
        } else {
            $this->updatePayment($transaction, $paymentData);
        }
    }

    /**
     * Cria pedido, transação e ciclo inicial registrando o primeiro estado do pagamento.
     */
    private function createPayment(PaymentDataDTO $paymentData): void
    {
        // Obtém o tenant
        $tenant = $this->tenantService->findTenant($paymentData->tenant_id);

        // Cria o pedido
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

        // Cria a transação
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

        // Sem ciclo não há o que sincronizar (ex: cobranças avulsas)
        if (!$paymentData->cycle) {
            return;
        }

        // Salva o ciclo e sincroniza os acessos do tenant
        $this->saveCycle($paymentData->subscription_id, $paymentData->cycle, $paymentData->provider);
        $this->syncTenantAccess($tenant, $paymentData->cycle);
    }

    /**
     * Atualiza transação e pedido já existentes, sincronizando o ciclo quando houver novidade.
     */
    private function updatePayment(OrderTransaction $transaction, PaymentDataDTO $paymentData): void
    {
        // Atualiza a transação
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

        // Atualiza o pedido
        $transaction->order->update([
            'status'           => $paymentData->order->status,
            'provider'         => $paymentData->provider,
            'provider_method'  => $paymentData->order->provider_method,
            'provider_message' => $paymentData->order->provider_message ?? $transaction->order->provider_message,
            'currency'         => $paymentData->order->currency,
            'total_amount'     => $paymentData->order->total_amount,
            'paid_at'          => $paymentData->order->paid_at ?? $transaction->order->paid_at,
        ]);

        if (!$paymentData->cycle) {
            return;
        }

        // Salva o ciclo e sincroniza os acessos do tenant
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

        // Cria o ciclo caso ainda não exista
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

        // Atualiza o status e as datas de cobrança do ciclo existente
        $cycle->update([
            'status'          => $cycleData->status,
            'billing_at'      => $cycleData->billing_at,
            'next_billing_at' => $cycleData->next_billing_at,
        ]);
    }

    /**
     * Reaplica os acessos do tenant após confirmação de cobrança.
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
