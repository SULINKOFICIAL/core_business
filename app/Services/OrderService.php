<?php

namespace App\Services;

use App\Models\ClientModule;
use App\Models\ClientPackage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientPackageItemConfiguration;
use App\Models\ClientSubscription;
use App\Models\Package;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\OrderTransaction;
use App\Models\SubscriptionCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderService
{

    /**
     * Cria um pedido em rascunho com base nos módulos e configurações.
     */
    public function getOrderInProgress($client, $package): Order
    {
        return Order::firstOrCreate(
            [
                'client_id' => $client->id,
                'package_id' => $package->id,
                'status' => 'draft',
            ],
        );
    }

    /**
     * Cria um pacote em rascunho com base nos módulos e configurações.
     */
    public function getPackageInProgress($client): ClientPackage
    {
        return ClientPackage::firstOrCreate(
            [
                'client_id' => $client->id,
                'progress'  => 'draft',
            ],
        );
    }

    /**
     * Recalcula o total do pedido considerando o cupom aplicado.
     */
    public function recalculateOrderTotals(Order $order, ?float $subtotal = null): void
    {

        // Soma o subtotal atual caso não seja informado
        $modulesSubtotal = $subtotal ?? (float) (($order->package?->modules()->sum('value')) ?? 0);
        $configurationSubtotal = $this->calculatePackageConfigurationSubtotal($order);
        $itemsSubtotal = $modulesSubtotal + $configurationSubtotal;

        // Calcula desconto do cupom quando existir
        $couponDiscount = $this->calculateCouponDiscount($order, $itemsSubtotal);

        // Calcula o total final do pedido
        $totalAmount = $itemsSubtotal - $couponDiscount;
        if ($totalAmount < 0) {
            $totalAmount = 0.0;
        }

        $order->update([
            'total_amount' => $totalAmount,
            'coupon_discount_amount' => $couponDiscount,
        ]);
    }

    /**
     * Soma efeitos de preco vindos das configuracoes dos itens do pacote.
     */
    private function calculatePackageConfigurationSubtotal(Order $order): float
    {
        if (!$order->package) {
            return 0.0;
        }

        $itemIds = $order->package->items()->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0.0;
        }

        $configs = ClientPackageItemConfiguration::whereIn('item_id', $itemIds)
            ->get(['derived_pricing_effect']);

        return (float) $configs->sum(function (ClientPackageItemConfiguration $config) {
            $price = data_get($config->derived_pricing_effect, 'price');
            return is_numeric($price) ? (float) $price : 0.0;
        });
    }

    public function createOrderPayment($package, $orderPayment, $client, $clientInfo, $card, $cvv = null, $intervalCycle, $address = null)
    {
        // Inicia o serviço da PagarMe
        $pagarMeService = new PagarMeService;

        /**
         * Retorna o customer na PagarMe 
         */
        $customer = $pagarMeService->findOrCreateCustomer([
            'id'           => $client->id,
            'name'         => $client->name ?? $clientInfo['name'],
            'email'        => $client->email ?? $clientInfo['email'],
            'type'         => (isset($client->company) || $clientInfo['type'] == 1) ? 'company' : 'individual',
            'document'     => isset($client->company) ? ($client->cnpj ?? $clientInfo['document']) : ($client->cpf ?? $clientInfo['document']),
            'country_code' => $clientInfo['phone']['country_code'],
            'area_code'    => $clientInfo['phone']['area_code'],
            'number'       => $clientInfo['phone']['phone'],
        ]);

        /**
         * Retorna o cartão na PagarMe 
         */
        $card = $pagarMeService->findOrCreateCard($client->id, $card->id, $cvv ?? null, $address ?? null);

        /**
         * Retorna a assinatura na PagarMe 
         */
        $subscription = $pagarMeService->createSubscription($customer['id'], $card['id'], $package, $orderPayment, $intervalCycle);

        // Se retornar sucesso da requisição
        if (isset($subscription) && isset($subscription['id'])) {

            // Atualiza ou cria a assinatura
            $subscription = Subscription::updateOrCreate([
                'pagarme_subscription_id' => $subscription['id'],
            ], [
                'pagarme_card_id'         => $subscription['card']['id'],
                'interval'                => $subscription['interval'],
                'payment_method'          => $subscription['payment_method'],
                'currency'                => $subscription['currency'],
                'installments'            => $subscription['installments'],
                'status'                  => $subscription['status'],
            ]);

            // Atualiza o pedido com o id da assinatura
            $orderPayment->update([
                'subscription_id' => $subscription->id,
            ]);

            // Busca a transação
            $transaction = $pagarMeService->getSubscriptionInvoices($subscription->pagarme_subscription_id);

            // Se retornar sucesso da requisição
            if (isset($transaction) && isset($transaction['data']) && isset($transaction['data'][0]['charge'])) {

                // Obtem o array de cobrança
                $charge = $transaction['data'][0]['charge'] ?? null;

                // Atualiza ou cria a transação
                OrderTransaction::updateOrCreate([
                    'order_id'                => $orderPayment->id,
                    'subscription_id'         => $subscription->id,
                    'pagarme_transaction_id'  => $charge['id'],
                ], [
                    'status'                  => $charge['status'],
                    'gateway_code'            => $charge['gateway_id'] ?? null,
                    'amount'                  => $charge['paid_amount'] / 100 ?? 0,
                    'currency'                => $charge['currency'] ?? null,
                    'method'                  => $charge['payment_method'] ?? null,
                    'recurrency'              => $charge['recurrence_cycle'] ?? null,
                    'response'                => json_encode($transaction),
                    'paid_at'                 => $charge['paid_at'] ?? null,

                ]);

                // Atualiza o status do pedido
                $orderPayment->update([
                    'status'  => $charge['status'],
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
                        'method'  => $charge['payment_method'],
                    ]);

                    // Cria ciclo
                    SubscriptionCycle::create([
                        'subscription_id' => $subscription->id,
                        'pagarme_cycle_id' => $transaction['cycle']['id'],
                        'start_date' => $transaction['cycle']['start_date'],
                        'end_date' => $transaction['cycle']['end_date'],
                        'status' => $transaction['cycle']['status'],
                        'cycle' => $transaction['cycle']['cycle'],
                        'billing_at' => $transaction['cycle']['billing_at'],
                        'next_billing_at' => $transaction['subscription']['next_billing_at'],
                    ]);
                }

                // Retorna a mensagem
                return $statusMap[$status]['message'];
            }
        }
    }

    public function confirmPaymentOrder($order)
    {

        // Verifica se o pagamento já foi processado
        if ($order->status === 'Pago') return 'Esse Pagamento já foi aprovado.';

        // Busca o cliente
        $client = $order->client;

        // Busca o pacote a ser renovado
        $package = Package::find($order->key_id);

        // Obtém a última assinatura
        $currentSubscription = $client->lastSubscription();

        /**
         * Caso não seja uma renovação, indica que o usuário esta 
         * trocando o pacote dele ou esta sendo atribuido um novo.
         */
        if ($order->type != 'Renovação') {

            // Cancela assinatura atual
            if ($currentSubscription) {
                $currentSubscription->update([
                    'status'   => 'Cancelado',
                    'end_date' => now(),
                ]);
            }

            // Remove módulos antigos
            ClientModule::where('client_id', $client->id)->delete();

            // Adiciona novos módulos
            foreach ($package->modules as $module) {
                ClientModule::create([
                    'client_id'  => $client->id,
                    'module_id'  => $module->id,
                ]);
            }

            // Define a data de inicio da nova assinatura para hoje
            $startDate = now();
        } else {

            // Muda o status da assinatura atual para renovada
            $currentSubscription->update([
                'status'   => 'Renovada',
            ]);

            // Extende a data da assinatura a partir da última
            $startDate = $currentSubscription->end_date;
        }

        // Verifique se a data final já passou
        if ($startDate->isPast()) $startDate = now();

        // Separa a data de encerramento
        $endDate = $startDate->clone();

        // Criar nova assinatura
        ClientSubscription::create([
            'client_id'  => $client->id,
            'package_id' => $package->id,
            'order_id'   => $order->id,
            'start_date' => $startDate,
            'end_date'   => $endDate->addDays($package->duration_days),
            'status'     => 'Ativo',
        ]);

        // Atualizar cliente com novo pacote
        $client->update([
            'package_id' => $package->id,
        ]);

        // Atualiza o pedido
        $order->status = 'Pago';
        $order->paid_at = now();
        $order->save();

        return 'Pacote "' . $package->name . '" ativado com sucesso.';
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
