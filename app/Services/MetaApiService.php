<?php

namespace App\Services;
use App\Services\RequestService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiService
{

    // Serviço Guzzle
    protected $RequestService;
    private $metaAppId;
    private $metaAppClientSecret;
    private $metaAppIdInstagram;
    private $metaAppClientSecretInstagram;

    public function __construct()
    {
        
        // Inicializa o serviço de requisições HTTP
        $this->RequestService = new RequestService();
        
        // Carrega credenciais do Meta App a partir do config/meta.php
        $this->metaAppId = config('meta.client_id');
        $this->metaAppClientSecret = config('meta.client_secret');


        $this->metaAppIdInstagram = config('meta.app_instagram_id');
        $this->metaAppClientSecretInstagram = config('meta.app_instagram_secret');

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
    public function getAccessToken($code, $type)
    {

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'GET',
            'https://graph.facebook.com/v20.0/oauth/access_token',
            [
                'query' => [
                    'code'           => $code,
                    'redirect_uri'   => route('callbacks.meta.' . $type),
                    'client_id'      => $this->metaAppId,
                    'client_secret'  => $this->metaAppClientSecret,
                ]
            ]
        );

        // Retorna a resposta
        return $response;

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
    public function getAccessTokenInstagram($code, $type)
    {
        $redirectUri = route('callbacks.meta.instagram');
        
        // Para Instagram Basic Display API, use este endpoint
        $params = [
            'client_id' => Config::get('meta.app_instagram_id'),
            'client_secret' => Config::get('meta.app_instagram_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code
        ];

        Log::info('Token Exchange Request', [
            'params' => $params,
            'redirect_uri' => $redirectUri
        ]);

        // Fazer a requisição para o endpoint correto
        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', $params);
        
        Log::info('Token Exchange Response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json()
            ];
        }

        return [
            'success' => false,
            'status' => $response->status(),
            'data' => $response->json()
        ];
    }


    /**
     * Busca dados do usuário autenticado.
     *
     * @param string $accessToken Token de acesso de curto prazo
     * @return array Resposta com dados do usuário autenticado
     */
    public function me($accessToken)
    {

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