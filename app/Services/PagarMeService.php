<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantCard;
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
    public function findOrCreateCustomer($clientInfo)
    {
        // Obtem o cliente
        $tenant = Tenant::find($clientInfo['id']);

        // Verifica se o cliente ja possui um customer na PagarMe
        if (isset($tenant) && $tenant->pagarme_customer_id) {

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->get($this->baseUrl . '/customers/' . $tenant->pagarme_customer_id)->json();

            // Monta o array
            $payload = [
                'name'      => $clientInfo['name'],
                'email'     => $clientInfo['email'],
                'document'  => $clientInfo['document'],
                'phones'    => [
                    'mobile_phone' => [
                        'country_code' => $clientInfo['country_code'],
                        'area_code'    => $clientInfo['area_code'],
                        'number'       => $clientInfo['number'],
                    ]
                ],
                'code'      => "client_{$clientInfo['id']}",
                'type'      => $clientInfo['type'],
            ];

            // Atualiza na PagarMe
            Http::withBasicAuth($this->apiKey, '')->put($this->baseUrl . '/customers/' . $response['id'], $payload);

            // Adiciona o email ao response
            $response['email'] = $tenant->email;

            // Verifica se a resposta foi bem sucedida
            if (isset($response) && isset($response['id'])) {
                return $response;
            }
        }

        // Se não possui cria um
        else {

            // Monta o payload para a criação do cliente
            $payload = [
                'name'      => $clientInfo['name'],
                'email'     => $clientInfo['email'],
                'document'  => $clientInfo['document'],
                'phones'    => [
                    'mobile_phone' => [
                        'country_code' => $clientInfo['country_code'],
                        'area_code'    => $clientInfo['area_code'],
                        'number'       => $clientInfo['number'],
                    ]
                ],
                'code'      => "client_{$clientInfo['id']}",
                'type'      => $clientInfo['type'],
            ];

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/customers', $payload)->json();

            // Verifica se a resposta foi bem sucedida
            if (isset($response) && isset($response['id'])) {

                // Atualiza o cliente com o id do customer na PagarMe
                $tenant->update([
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
     * Função responsável por
     * 
     *? Verificar se o cartão está atrelado a um customer na PagarMe
     * 
     *? Se nao existir, cria um
     */
    public function findOrCreateCard($clientId, $cardId, $cvv = null, $address = null)
    {
        // Obtem o cliente
        $tenant = Tenant::find($clientId);

        // Verifica se o cliente já tem um cartão na PagarMe
        $card = TenantCard::find($cardId);

        // Verifica se o cartão ja existe
        if (isset($card) && $card->pagarme_card_id) {

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->get($this->baseUrl . '/customers/' . $tenant->pagarme_customer_id . '/cards/' . $card->pagarme_card_id)->json();

            // Verifica se a resposta foi bem sucedida
            if (isset($response) && isset($response['id'])) {
                return $response;
            }
        }

        // Se nao possui cria um
        else {

            // Monta o payload para a criação do cliente
            $payload = [
                'number'          => $card->number,
                'holder_name'     => $card->name,
                'exp_month'       => $card->expiration_month,
                'exp_year'        => $card->expiration_year,
                'cvv'             => $cvv,
                'billing_address' => [
                    'line_1'   => $address['line_1'],
                    'zip_code' => $address['zip_code'],
                    'city'     => $address['city'],
                    'state'    => $address['state'],
                    'country'  => $address['country'],
                ]
            ];

            // Cria o cliente na PagarMe
            $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/customers/' . $tenant->pagarme_customer_id . '/cards', $payload)->json();

            // Verifica se a resposta foi bem sucedida
            if (isset($response) && isset($response['id'])) {

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
    public function createSubscription($customerId, $cardId, $package, $order, $intervalCycle)
    {

        /**
         * Cria um array de items obrigatorio para o plano
         * Cria um item com o preço total do pedido
         */
        $items = [
            [
                'description'   => 'Assinatura Mi.Core - Plano Personalizado',
                'quantity'      => 1,
                'pricing_scheme' => [
                    'scheme_type' => 'unit',
                    'price'       => $order->total_amount * 100,
                ],
            ]
        ];

        // Cria uma descrição com o nome de todos os modulos
        $description = "Modulos do plano: " . implode(', ', $package->modules->pluck('name')->toArray());

        // Monta o payload para a criação do plano
        $payload = [
            'name'           => "Assinatura Tenant - {$package->tenant->name} #{$package->tenant->id}",
            'description'    => $description,
            'interval'       => $intervalCycle,
            'interval_count' => 1,
            'billing_type'   => 'prepaid',
            'currency'       => $order->currency ?? 'BRL',
            'installments'   => 1,
            'items'          => $items,
            'customer_id'    => $customerId,
            'payment_method' => 'credit_card',
            'card_id'        => $cardId
        ];

        // Cria a assinatura na PagarMe
        $response = Http::withBasicAuth($this->apiKey, '')->post($this->baseUrl . '/subscriptions', $payload)->json();

        // Verifica se a resposta foi bem sucedida
        if (isset($response) && isset($response['id'])) {
            $package->update([
                'name' => $items[0]['description'],
            ]);

            return $response;
        }
    }

    public function getSubscriptionInvoices(string $subscriptionId)
    {
        return Http::withBasicAuth($this->apiKey, '')->get("{$this->baseUrl}/invoices", ['subscription_id' => $subscriptionId])->json();
    }

    public function findSubscription($subscriptionId)
    {
        return Http::withBasicAuth($this->apiKey, '')->get("{$this->baseUrl}/subscriptions/{$subscriptionId}")->json();
    }

    public function cancelSubscription($subscriptionId)
    {
        return Http::withBasicAuth($this->apiKey, '')->delete("{$this->baseUrl}/subscriptions/{$subscriptionId}")->json();
    }
}
