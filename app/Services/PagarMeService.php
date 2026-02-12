<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientCard;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PagarMeService
{    
    protected string $apiKey;
    protected string $baseUrl = 'https://api.pagar.me/core/v5';

    public function __construct()
    {
        $this->apiKey = config('pagarme.api_key');
    }

    /**
     ** Função responsável por
     * 
     *? Verificar se o cliente está atrelado a um customer na PagarMe
     * 
     *? Se nao existir, cria um
     */
    public function findOrCreateCustomer($clientId)
    {
        // Obtem o cliente
        $client = Client::find($clientId);

        // Verifica se o cliente ja possui um customer na PagarMe
        if(isset($client) && $client->pagarme_customer_id) {
            
            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->get($this->baseUrl . '/customers/' . $client->pagarme_customer_id)->json();

            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {
                return $response;
            }

        }
        
        // Se não possui cria um
        else {

            // Monta o payload para a criação do cliente
            $payload = [
                'name'      => $client->name,
                'email'     => $client->email,
                'document'  => '09842558932',
                'phones'    => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code'    => '41',
                        'number'       => '996718404',
                    ]
                ],
                'code'      => "client_{$client->id}",
                'type'      => 'individual',
            ];

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/customers', $payload)->json();

            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {

                // Atualiza o cliente com o id do customer na PagarMe
                $client->update([
                    'pagarme_customer_id' => $response['id']
                ]);

                // Retorna o id do customer
                return $response;
            }

        }

        // Retorna nulo caso nao encontre
        return null;

    }

    /**
     ** Função responsável por
     * 
     *? Verificar se o pedido está atrelado a um plano na PagarMe
     * 
     *? Se nao existir, cria um
     */
    public function findOrCreatePlan($orderId)
    {
        // Obtem o pedido
        $order = Order::find($orderId);

        // Verifica se o pedido ja possui um plano na PagarMe
        if(isset($order) && $order->pagarme_plan_id) {

            // Cria o plano na PagarMe 
            $response = Http::withBasicAuth($this->apiKey, '')->get($this->baseUrl . '/plans/' . $order->pagarme_plan_id)->json();
            
            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {
                return $response;
            }

        }

        // Se nao possui cria um
        else {

            /**
             * Cria um array de items obrigatorio para o plano
             * Cria um item com o preço total do pedido
             */
            $items = [
                [
                    'name'            => 'Assinatura Mi.Core - Plano Personalizado',
                    'quantity'        => 1,
                    'pricing_scheme'  => [
                        'scheme_type' => 'unit',
                        'price'       => $order->total_amount * 100,
                    ],
                ]
            ];
            
            // Cria uma descrição com o nome de todos os modulos
            $description = "Modulos do plano: " . implode(', ', $order->items->pluck('item_name')->toArray());

            // Monta o payload para a criação do plano
            $payload = [
                'name'             => "Assinatura Cliente - {$order->client->name} #{$order->client->id}",
                'description'      => $description,
                'payment_methods'  => ['credit_card'],
                'interval'         => 'month',
                'interval_count'   => 1,
                'billing_type'     => 'prepaid',
                'currency'         => $order->currency,
                'installments'     => [1],
                'items'            => $items,
            ];

            // Cria o plano na PagarMe 
            $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/plans', $payload)->json();

            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {

                // Atualiza o pedido com o id do plano na PagarMe
                $order->update([
                    'pagarme_plan_id' => $response['id']
                ]);

                // Retorna o id do plano
                return $response;
            }

        }
    }

    /**
     * Função responsável por
     * 
     *? Verificar se o cartão está atrelado a um customer na PagarMe
     * 
     *? Se nao existir, cria um
     */
    public function findOrCreateCard($clientId, $cardId)
    {
        // Obtem o cliente
        $client = Client::find($clientId);

        // Verifica se o cliente já tem um cartão na PagarMe
        $card = ClientCard::find($cardId);

        // Verifica se o cartão ja existe
        if(isset($card) && $card->pagarme_card_id) {

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->get($this->baseUrl . '/customers/' . $client->pagarme_customer_id . '/cards/' . $card->pagarme_card_id)->json();

            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {
                return $response;
            }

        }

        // Se nao possui cria um
        else {
            
            // Monta o payload para a criação do cliente
            $payload = [
                'number'          => '4000000000000010',
                'holder_name'     => $client->name,
                'exp_month'       => 12,
                'exp_year'        => 2030,
                'cvv'             => '123',
                'billing_address' => [
                    'line_1'   => 'Rua Rio Uruguai, 123',
                    'zip_code' => '83322220',
                    'city'     => 'Pinhais',
                    'state'    => 'PR',
                    'country'  => 'BR',
                ]
            ];

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/customers/' . $client->pagarme_customer_id . '/cards', $payload)->json();

            // Verifica se a resposta foi bem sucedida
            if(isset($response) && isset($response['id'])) {

                // Atualiza o cliente com o id do customer na PagarMe
                $card->update([
                    'pagarme_card_id' => $response['id']
                ]);

                // Retorna o id do customer
                return $response;
            }

        }
    }

    /**
     ** Função responsável por
     * 
     *? Verificar se a assinatura está atrelada a um plano na PagarMe
     * 
     *? Se nao existir, cria um
     */
    public function findOrCreateSubscription($planId, $customerId, $cardId)
    {
        // Monta o array para a criação da assinatura
        $payload = [
            'plan_id'         => $planId,
            'customer_id'     => $customerId,
            'payment_method'  => 'credit_card',
            'card_id'         => $cardId,
        ];

        // Cria a assinatura na PagarMe
        $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/subscriptions', $payload)->json();

        // Verifica se a resposta foi bem sucedida
        if(isset($response) && isset($response['id'])) {
            return $response;
        }

    }
}