<?php

namespace App\Services;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ConnectException;

/**
 * Classe responsável por interagir com a API da eRede para realizar operações
 * relacionadas a transações e tokenização de cartões de crédito.
 * Documentação oficial: https://developer.userede.com.br/e-rede
 */
class GuzzleService
{
    /**
     * Realiza uma solicitação Guzzle com autenticação Bearer
     *
     * @param string $method Método HTTP (get, post, etc)
     * @param string $url URL para a solicitação
     * @param object $client Objeto cliente contendo informações do cliente
     * @param array|null $data Dados opcionais para incluir na requisição
     * @return array Resposta da API
     */
    public function request($method, $url, $client, $data = null)
    {
        // Instancia o Guzzle
        $guzzle = new guzzle();

        // Inicializa os parâmetros da requisição
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . env('CENTRAL_TOKEN'),
            ],
            'timeout' => 5
        ];

        // Se houver dados, adiciona ao corpo da requisição
        if ($data !== null) {
            $options['json'] = $data;
        }
        
        try {
            dd("http://{$client->domains[0]->domain}/api/$url", $options);
            // Realiza a solicitação
            $response = $guzzle->$method("http://{$client->domains[0]->domain}/api/$url", $options);

            // Obtém o corpo da resposta
            $response = $response->getBody()->getContents();

            return [
                'success' => true,
            ];
    
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}