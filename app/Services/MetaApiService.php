<?php

namespace App\Services;
use App\Services\RequestService;

class MetaApiService
{

    // Serviço Guzzle
    protected $RequestService;
    private $metaAppId;
    private $metaAppClientSecret;

    public function __construct()
    {
        
        // Inicializa o serviço de requisições HTTP
        $this->RequestService = new RequestService();
        
        // Carrega credenciais do Meta App a partir do config/meta.php
        $this->metaAppId = config('meta.client_id');
        $this->metaAppClientSecret = config('meta.client_secret');

    }

    /**
     * Troca o código de autorização (code) pelo token de acesso.
     * 
     * Este método deve ser chamado logo após o usuário autorizar a aplicação.
     * Ele faz a requisição ao endpoint do Meta OAuth para obter o access_token.
     *
     * @param string $code Código de autorização retornado pelo Meta
     * @return array Resposta com token de acesso e metadados
     */
    public function getAccessToken($code)
    {

        // Retorno temporário
        return [
            "success" => true,
            "status" => 200,
            "data" => [
                "access_token" => "EAA3tm712Cw8BP9gtTDVKicZBSm0h5tPf4ZBEpKsZCbcIcusg2jJ0C4lUSWOmE1CnDP8QoFkvDvedZCguZBiZCJC9gnwxu1oJ8LIYsP8DFLvClN5ZB0oFUZCJZBdARhZAP2ZAxZA2UY1gJUejkzihBttXh2rl02JOhjZCkTz6T7vp9y0ElT1gzGY7ZBxcGjXKZAN36z4WPH5ZB0uPTrMhDT61YVphRkqc5M5ZCN8H5V4RXAeDIwS9RvyXMdbJyukMYVmxJEKnTBOh0ng85YGZBVm4J2jZCQQqaIXoxn1wK50sldjaFqU",
                "token_type" => "bearer",
                "expires_in" => 5105318,
            ],
            "headers" => [],
        ];

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            'https://graph.facebook.com/v20.0/oauth/access_token',
            [
                'query' => [
                    'code'           => $code,
                    'redirect_uri'   => route('callbacks.meta'),
                    'client_id'      => $this->metaAppId,
                    'client_secret'  => $this->metaAppClientSecret,
                ]
            ]
        );

        // Retorna a resposta
        return $response;

    }

    /**
     * Busca dados do usuário autenticado.
     *
     * @param string $accessToken Token de acesso de curto prazo
     * @return array Resposta com dados do usuário autenticado
     */
    public function me($accessToken)
    {

        return [
            "success" => true,
            "status" => 200,
            "data" => [
                "id" => "2302152953596620",
                "name" => "Jeandreo Furquim",
            ],
            "headers" => [],
        ];

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            'https://graph.facebook.com/v20.0/me?fields=id,name',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]
        );

        // Retorna a resposta
        return $response;

    }

    /**
     * Converte um token de curto prazo em um token de longo prazo.
     * 
     * O Meta emite tokens temporários após o login. Este método realiza
     * o “exchange” para um token de longa duração (geralmente 60 dias).
     *
     * @param string $accessToken Token de acesso de curto prazo
     * @return array Resposta com novo token e validade
     */
    public function getLongToken($accessToken)
    {
        
        return [
            "success" => true,
            "status" => 200,
            "data" => [
                "access_token" => "EAA3tm712Cw8BP8VoAXbKEtsp3qGz2JdkiaVk3zI58RPZBCxd82ULEt4tm3pllKH399Tz2iJQt7MlrK4quzFmF9BW5eQRcxr9OhTj0Ri7ptLIv3dZBAlV8Rj3yAozPlrhYAzMUz9wSAPlJ7B3t7OuFlOUXdS9qpx442uKHFkMcTpK5wiAWFd6X5kCgn",
                "token_type" => "bearer",
                "expires_in" => 5105318,
            ],
            "headers" => [],
        ];

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            'https://graph.facebook.com/v20.0/oauth/access_token',
            [
                'query' => [
                    'grant_type'        => 'fb_exchange_token',
                    'client_id'         => $this->metaAppId,
                    'client_secret'     => $this->metaAppClientSecret,
                    'fb_exchange_token' => $accessToken,
                ]
            ]
        );

        // Retorna a resposta
        return $response;

    }

    /**
     * Recupera a lista de contas Business associadas ao usuário autenticado.
     * 
     * Utiliza o token de acesso para consultar as contas do tipo "Business Manager"
     * vinculadas ao usuário logado.
     *
     * @param string $accessToken Token de acesso válido
     * @return array Lista de empresas com ID e nome
     */
    public function getBusinesses($accessToken)
    {

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            'https://graph.facebook.com/v20.0/me/businesses?fields=id,name,profile_picture_uri',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]
        );

        // Retorna a resposta
        return $response;

    }

    /**
     * Recupera a lista de números de WhatsApp Business associados a uma conta Business.
     *
     * @param string $accessToken Token de acesso de curto prazo
     * @param string $businessId ID da conta Business
     * @return array Resposta com dados do usuário autenticado
     */
    public function getWabas($accessToken, $businessId)
    {

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            "https://graph.facebook.com/v20.0/{$businessId}/owned_whatsapp_business_accounts?fields=id,name,phone_numbers,link,profile_picture_uri",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ],
        );

        // Retorna a resposta
        return $response;

    }

}