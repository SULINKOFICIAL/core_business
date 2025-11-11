<?php

namespace App\Services\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Classe responsável por centralizar todas as requisições HTTP feitas via Guzzle.
 *
 * Objetivo:
 * - Padronizar requisições externas (Meta, HubSpot, MercadoLivre, etc)
 * - Facilitar logs e tratamento de erros
 * - Evitar duplicação de código nos outros Services
 */
class RequestService
{
    /**
     * Instância principal do Guzzle Client.
     */
    protected $client;

    /**
     * Construtor básico.
     *
     * @param array $config Configurações opcionais para o Guzzle (ex: base_uri, timeout)
     *
     * Exemplo:
     * new RequestService(['base_uri' => 'https://graph.facebook.com/v21.0/']);
     */
    public function __construct(array $config = [])
    {
        /**
         * Configurações padrão do Guzzle
         */
        $defaultConfig = [
            'timeout' => 15,            // Tempo máximo de resposta (segundos)
            'verify' => false,          // Evita problemas com certificados SSL em sandbox
            'http_errors' => false,     // Não lança exceções automaticamente, pois o código já trata.
        ];

        $this->client = new Client(array_merge($defaultConfig, $config));
    }

    /**
     * Realiza uma requisição genérica usando Guzzle.
     *
     * @param string $method  Método HTTP (GET, POST, etc.)
     * @param string $url     URL completa
     * @param array  $options Opções padrão do Guzzle (json, headers, etc.)
     * @param string|null $token Token opcional para adicionar Bearer automático
     */
    public function request(string $method, string $url, array $options = [])
    {

        try {

            /**
             * Envia a requisição
             */
            $response = $this->client->request($method, $url, $options);
            
            // Não lê o corpo se estiver usando sink/stream
            $isStreaming = isset($options['sink']) || ($options['stream'] ?? false) === true;
            
            /**
             * Se a requisicao for stream, o corpo não precisa ser capturado,
             * podem ser usados para download de arquivos, etc.
             * 
             * Caso contrario, ele lê o corpo da requisicao e devolve a resposta
             * CRU ou decodifica para array.
             */
            if ($isStreaming) {
                $body = null;
            } elseif (isset($options['raw']) && $options['raw']) {
                $body = $response->getBody()->getContents();
            } else {
                $body = json_decode($response->getBody()->getContents(), true);
            }

            // Extrai headers se solicitado
            $headers = [];
            if (!empty($options['return_headers']) && $options['return_headers'] === true) {
                foreach ($response->getHeaders() as $key => $values) {
                    $headers[$key] = implode(', ', $values);
                }
            }

            /**
             * Retorno padronizado em caso de sucesso
             */
            return [
                'success' => true,
                'status'  => $response->getStatusCode(),
                'data'    => $body,
                'headers' => $headers,
            ];

        } catch (RequestException $e) {

            /**
             * Caso ocorra erro na requisição (ex: timeout, 400, 401, 500),
             * o Guzzle lança uma exceção que tratamos aqui.
             */
            $responseBody = optional($e->getResponse())->getBody()->getContents();

            /**
             * Loga no Laravel (storage/logs/laravel.log)
             */
            Log::error('API Request Failed', [
                'url'      => $url,
                'method'   => $method,
                'error'    => $e->getMessage(),
                'status'   => optional($e->getResponse())->getStatusCode(),
                'response' => $responseBody,
            ]);

            /**
             * Retorno padronizado em caso de erro
             */
            return [
                'success' => false,
                'status'  => optional($e->getResponse())->getStatusCode(),
                'error'   => $e->getMessage(),
                'body'    => $responseBody,
            ];
        }
    }
}
