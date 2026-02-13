<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderSubscription;
use App\Models\OrderTransaction;
use App\Services\PagarMeService;
use Carbon\Carbon;

class DeveloperController extends Controller
{
    public function test()
    {
        // Obtem o pedido
        $order = Order::find(1);

        // Obtem o cliente
        $client = $order->client;

        /**
         * Retorna o customer na PagarMe 
         */
        $customerId = (new PagarMeService())->findOrCreateCustomer($client->id);

        /**
         * Retorna o cartão na PagarMe 
         */
        $cardId = (new PagarMeService())->findOrCreateCard($client->id, 1);

        /**
         * Retorna a assinatura na PagarMe 
         */
        $subscription = (new PagarMeService())->findOrCreateSubscription($customerId['id'], $cardId['id'], $order);

        $orderSubscription = OrderSubscription::updateOrCreate([
            'order_id'                => $order->id,
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

        $transaction = (new PagarMeService())->getSubscriptionInvoices($orderSubscription->pagarme_subscription_id);

        $charge = $transaction['data'][0]['charge'] ?? null;

        $orderCharge = OrderTransaction::updateOrCreate([
            'subscription_id'         => $orderSubscription->id,
            'pagarme_transaction_id'  => $charge['id'],
        ], [
            'gateway_code'            => $charge['gateway_id'],
            'amount'                  => $charge['paid_amount'],
            'currency'                => $charge['currency'],
            'method'                  => $charge['payment_method'],
            'response'                => $transaction,
            'response'                => json_encode($transaction),

        ]);

        dd($orderCharge);

    }
}