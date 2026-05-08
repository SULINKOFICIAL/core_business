<?php

namespace App\Services;

use App\Models\TenantPlan;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\OrderTransaction;
use App\Models\SubscriptionCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\TenantConfigurationSyncService;

class OrderService
{
    public function __construct(private TenantConfigurationSyncService $syncService)
    {
    }

    /**
     * Cria um pedido em rascunho com base nos módulos e configurações.
     */
    public function getOrderInProgress($tenant, $plan): Order
    {
        return Order::firstOrCreate(
            [
                'tenant_id'  => $tenant->id,
                'plan_id'    => $plan->id,
                'status'     => 'draft',
            ],
        );
    }

    /**
     * Cria um pacote em rascunho com base nos módulos e configurações.
     */
    public function getPlanInProgress($tenant): TenantPlan
    {
        return TenantPlan::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'progress'  => 'draft',
            ],
        );
    }

    /**
     * Recalcula o total do pedido considerando o cupom aplicado.
     */
    public function recalculateOrderTotals(Order $order, ?float $subtotal = null): void
    {
        // Soma o subtotal dos itens com base no preço aplicado canônico
        $itemsSubtotal = $subtotal ?? $this->calculateItemsSubtotal($order);

        // Calcula o desconto do cupom quando existir
        $couponDiscount = $this->calculateCouponDiscount($order, $itemsSubtotal);

        // Calcula o total final do pedido
        $totalAmount = max(0.0, $itemsSubtotal - $couponDiscount);

        $order->update([
            'total_amount' => $totalAmount,
            'coupon_discount_amount' => $couponDiscount,
        ]);
    }

    /**
     * Calcula subtotal do rascunho pela soma de applied_price dos itens.
     */
    private function calculateItemsSubtotal(Order $order): float
    {
        if (!$order->plan) {
            return 0.0;
        }

        return (float) $order->plan->items()->sum('applied_price');
    }

    public function createOrderPayment($plan, $orderPayment, $tenant, $clientInfo, $card, $cvv = null, $intervalCycle)
    {

        // Inicia o serviço da PagarMe
        $pagarMeService = new PagarMeService;

        /**
         * Retorna o customer na PagarMe 
         */
        $customer = $pagarMeService->findOrCreateCustomer([
            'id'           => $tenant->id,
            'name'         => $tenant->name ?? $clientInfo['name'],
            'email'        => $tenant->email ?? $clientInfo['email'],
            'type'         => (isset($tenant->company) || $clientInfo['type'] == 1) ? 'company' : 'individual',
            'document'     => isset($tenant->company) ? ($tenant->cnpj ?? $clientInfo['document']) : ($tenant->cpf ?? $clientInfo['document']),
            'country_code' => $clientInfo['phone']['country_code'],
            'area_code'    => $clientInfo['phone']['area_code'],
            'number'       => $clientInfo['phone']['phone'],
        ]);

        /**
         * Retorna o cartão na PagarMe 
         */
        $card = $pagarMeService->findOrCreateCard($tenant->id, $card->id, $cvv ?? null, $clientInfo['address']);

        /**
         * Retorna a assinatura na PagarMe 
         */
        $subscription = $pagarMeService->createSubscription($customer['id'], $card['id'], $plan, $orderPayment, $intervalCycle);

        // Se retornar sucesso da requisição
        if (isset($subscription) && isset($subscription['id'])) {

            // Atualiza ou cria a assinatura
            $subscription = Subscription::updateOrCreate([
                'provider'                => 'pagarme',
                'provider_subscription_id'=> $subscription['id'],
            ], [
                'provider_card_id'        => $subscription['card']['id'],
                'interval'                => $subscription['interval'],
                'payment_method'          => $subscription['payment_method'],
                'currency'                => $subscription['currency'],
                'installments'            => $subscription['installments'],
                'status'                  => $subscription['status'],
            ]);

            // Obtem o ultimo pedido pago
            $lastOrder = Order::where('tenant_id', $tenant->id)
                ->where('status', 'paid')
                ->orderBy('created_at', 'desc')
                ->first();

            if(isset($lastOrder) && isset($lastOrder->subscription) && $lastOrder->subscription->provider_subscription_id) {
                
                // Obtem a assinatura do ultimo pedido pago
                $lastSubscription = $lastOrder->subscription;

                // Cancela a assinatura do ultimo pedido pago
                $pagarMeService->cancelSubscription($lastSubscription->provider_subscription_id);

            }

            // Atualiza o pedido com o id da assinatura
            $orderPayment->update([
                'subscription_id' => $subscription->id,
            ]);

            /**
             * Busca o pedido de assinatura, cria transação e ciclo. 
             */
            return $this->processSubscriptionPayment($orderPayment, $subscription, $plan);

        }
    }

    public function processSubscriptionPayment($orderPayment, $subscription, $plan)
    {

        // Inicia o serviço da PagarMe
        $pagarMeService = new PagarMeService;

        // Busca o pedido de assinatura
        $transaction = $pagarMeService->getSubscriptionInvoices($subscription->provider_subscription_id);

        // Se retornar sucesso da requisição
        if (isset($transaction) && isset($transaction['data']) && isset($transaction['data'][0]['charge'])) {

            // Obtem o array de cobrança
            $charge = $transaction['data'][0]['charge'] ?? null;

            // Atualiza ou cria a transação
            OrderTransaction::updateOrCreate([
                'order_id'                => $orderPayment->id,
                'subscription_id'         => $subscription->id,
                'provider'                => 'pagarme',
                'provider_transaction_id' => $charge['id'],
            ], [
                'status'                  => $charge['status'],
                'gateway_code'            => $charge['gateway_id'] ?? null,
                'amount'                  => $charge['paid_amount'] / 100 ?? 0,
                'currency'                => $charge['currency'] ?? null,
                'provider_method'         => $charge['payment_method'] ?? null,
                'recurrency'              => $charge['recurrence_cycle'] ?? null,
                'response'                => $transaction,
                'paid_at'                 => $charge['paid_at'] ?? null,

            ]);

            // Atualiza o status do pedido
            $orderPayment->update([
                'status'  => $charge['status'],
            ]);

            $plan->update([
                'progress' => $charge['status'] == 'paid' ? 'completed' : 'draft',
            ]);

            // Mapeia de acordo com o status
            $statusMap = [
                'paid' => [
                    'subscription_status' => 'active',
                    'message' => 'Assinatura aprovada com sucesso.',
                ],
                'pending' => [
                    'subscription_status' => 'pending',
                    'message' => 'Pagamento pendente.',
                ],
                'processing' => [
                    'subscription_status' => 'pending',
                    'message' => 'Pagamento em processamento.',
                ],
                'failed' => [
                    'subscription_status' => 'failed',
                    'message' => 'Pagamento recusado.',
                ],
                'canceled' => [
                    'subscription_status' => 'canceled',
                    'message' => 'Pagamento cancelado.',
                ],
                'refunded' => [
                    'subscription_status' => 'refunded',
                    'message' => 'Pagamento estornado.',
                ],
            ];

            // Pega o status da transação
            $status = $charge['status'];

            // Verifica se o status existe no mapeamento
            if (!isset($statusMap[$status])) {
                return 'Status desconhecido.';
            }

            // Atualiza assinatura
            $subscription->update([
                'status' => $statusMap[$status]['subscription_status'],
            ]);

            // Se for pago, atualiza dados extras
            if ($status === 'paid') {
                $orderPayment->update([
                    'paid_at' => $charge['paid_at'],
                    'provider_method' => $charge['payment_method'],
                ]);

                // Extrai resposta
                $transaction = $transaction['data'][0];

                // Cria ciclo
                SubscriptionCycle::updateOrCreate([
                    'provider'          => 'pagarme',
                    'provider_cycle_id' => $transaction['cycle']['id'],
                ],[
                    'subscription_id'   => $subscription->id,
                    'start_date'        => $transaction['cycle']['start_at'],
                    'end_date'          => $transaction['cycle']['end_at'],
                    'status'            => $transaction['cycle']['status'],
                    'cycle'             => $transaction['cycle']['cycle'],
                    'billing_at'        => $transaction['cycle']['billing_at'],
                    'next_billing_at'   => $transaction['subscription']['next_billing_at'],
                ]);

                /**
                 * Após pagamento aprovado, propaga ao tenant remoto
                 * o estado consolidado de módulos, vigência e limites.
                 */
                $this->syncService->syncFromCurrentPlan(
                    $orderPayment->tenant,
                    source: 'order_paid',
                    operatorId: null,
                    reason: 'Pagamento aprovado',
                    startDate: $transaction['cycle']['start_at'] ?? null,
                    endDate: $transaction['cycle']['end_at'] ?? null,
                );

            }

            // Retorna a mensagem
            return $statusMap[$status]['message'];
        }
        
    }

    /**
     * Calcula o desconto do cupom aplicado no pedido.
     */
    private function calculateCouponDiscount(Order $order, float $subtotal): float
    {

        if (!$order->coupon_id || !$order->coupon_type_snapshot) {
            return 0.0;
        }

        $type = $order->coupon_type_snapshot;
        $value = (float) ($order->coupon_value_snapshot ?? 0);

        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($type === 'percent') {
            $discount = $subtotal * ($value / 100);
        } elseif ($type === 'fixed') {
            $discount = $value;
        } elseif ($type === 'trial') {
            $discount = $subtotal;
        } else {
            $discount = 0.0;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return $discount;
    }
}
