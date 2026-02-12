<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PagarMeService;

class DeveloperController extends Controller
{
    public function test()
    {
        // Obtem o pedido
        $order = Order::find(1);

        // Obtem o cliente
        $client = $order->client;

        /**
         * Retorna o plano na PagarMe  
         */
        $planId = (new PagarMeService())->findOrCreatePlan($order->id);

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
        $subscriptionId = (new PagarMeService())->findOrCreateSubscription($planId['id'], $customerId['id'], $cardId['id']);

        dd($subscriptionId);


    }

    private function infos()
    {
        'cus_RLg5KR3fZDTJJPdw';
    }
}