<?php

namespace App\Services;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;

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
        $guzzle = new Guzzle();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . env('CENTRAL_TOKEN'),
            ],
            'timeout' => 5,
        ];

        if ($data !== null) {
            $options['json'] = $data;
        }

        try {
            $response = $guzzle->$method("http://{$client->domains[0]->domain}/api/$url", $options);
            $body = $response->getBody()->getContents();

            return [
                'success' => true,
                'data' => $body,
            ];

        } catch (ConnectException $e) {
            return [
                'success' => false,
                'message' => 'Falha de conexão: ' . $e->getMessage(),
            ];
        } catch (ClientException | ServerException | RequestException $e) {
            // Captura qualquer erro HTTP e retorna sem quebrar o fluxo
            $response = $e->getResponse();
            $status = $response ? $response->getStatusCode() : null;
            $body = $response ? $response->getBody()->getContents() : null;
            return [
                'success' => false,
                'message' => "Erro HTTP {$status}",
            ];
        }
    }
}