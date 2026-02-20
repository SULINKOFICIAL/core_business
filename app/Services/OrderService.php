<?php

namespace App\Services;

use App\Models\ClientModule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemConfiguration;
use App\Models\ClientSubscription;
use App\Models\Package;
use App\Models\Module;
use App\Models\OrderSubscription;
use App\Models\OrderTransaction;
use Carbon\Carbon;

class OrderService
{

    public function createOrderPayment($orderPayment, $client, $card, $cvv, $intervalCicle, $address)
    {
        $pagarMeService = new PagarMeService;

        /**
         * Retorna o customer na PagarMe 
         */
        $customer = $pagarMeService->findOrCreateCustomer($client->id);

        /**
         * Retorna o cartão na PagarMe 
         */
        $card = $pagarMeService->findOrCreateCard($client->id, $card->id, $cvv, $address);

        /**
         * Retorna a assinatura na PagarMe 
         */
        $subscription = $pagarMeService->createSubscription($customer['id'], $card['id'], $orderPayment, $intervalCicle);

        if(isset($subscription) && isset($subscription['id'])) {

            $orderSubscription = OrderSubscription::updateOrCreate([
                'order_id'                => $orderPayment->id,
                'pagarme_subscription_id' => $subscription['id'],
            ], [
                'pagarme_card_id'         => $subscription['card']['id'],
                'interval'                => $subscription['interval'],
                'payment_method'          => $subscription['payment_method'],
                'currency'                => $subscription['currency'],
                'installments'            => $subscription['installments'],
                'status'                  => $subscription['status'],
                'billing_at'              => Carbon::parse($subscription['current_cycle']['billing_at']),
                'next_billing_at'         => Carbon::parse($subscription['next_billing_at']),
            ]);

            $transaction = $pagarMeService->getSubscriptionInvoices($orderSubscription->pagarme_subscription_id);

            if(isset($transaction) && isset($transaction['data']) && isset($transaction['data'][0]['charge'])) {

                $charge = $transaction['data'][0]['charge'] ?? null;

                OrderTransaction::updateOrCreate([
                    'subscription_id'         => $orderSubscription->id,
                    'pagarme_transaction_id'  => $charge['id'],
                ], [
                    'status'                  => $charge['status'],
                    'gateway_code'            => $charge['gateway_id'],
                    'amount'                  => $charge['paid_amount'] / 100,
                    'currency'                => $charge['currency'],
                    'method'                  => $charge['payment_method'],
                    'recurrency'              => $charge['recurrence_cycle'],
                    'response'                => json_encode($transaction),
                    'paid_at'                 => $charge['paid_at'],
        
                ]);

                $orderPayment->update([
                    'status'  => $charge['status'],
                ]);

                switch ($charge['status']) {

                    case 'paid':
                        $orderSubscription->update([
                            'status' => 'active',
                        ]);

                        $orderPayment->update([
                            'paid_at' => $charge['paid_at'],
                            'method'  => $charge['payment_method'],
                        ]);

                        return 'Assinatura aprovada com sucesso.';
                        break;
                
                    case 'pending':

                        $orderSubscription->update([
                            'status' => 'pending',
                        ]);

                        return 'Pagamento pendente.';

                        break;
                

                    case 'processing':
                        $orderSubscription->update([
                            'status' => 'pending',
                        ]);

                        return 'Pagamento em processamento.';

                        break;
                
                    case 'failed':
                        $orderSubscription->update([
                            'status' => 'failed',
                        ]);

                        return 'Pagamento recusado.';
                        
                        break;
                
                    case 'canceled':
                        $orderSubscription->update([
                            'status' => 'canceled',
                        ]);

                        return 'Pagamento cancelado.';

                        break;
                
                    case 'refunded':
                        $orderSubscription->update([
                            'status' => 'refunded',
                        ]);

                        return 'Pagamento estornado.';

                        break;

                    default:
                        return 'Status desconhecido.';
                }
                
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
        if($order->type != 'Renovação'){

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
     * Cria um pedido em rascunho com base nos módulos e configurações.
     */
    public function getOrderInProgress($client): Order
    {
        return Order::firstOrCreate(
            [
                'client_id' => $client->id,
                'status' => 'draft',
            ],
        );
    }

    /**
     * Recalcula o total do pedido considerando o cupom aplicado.
     */
    public function recalculateOrderTotals(Order $order, ?float $subtotal = null): void
    {

        // Soma o subtotal atual caso não seja informado
        $itemsSubtotal = $subtotal ?? (float) $order->items()->sum('amount');

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
