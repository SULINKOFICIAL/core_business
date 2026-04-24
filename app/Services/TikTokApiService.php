<?php

namespace App\Services;
use App\Services\RequestService;

class TikTokApiService
{

    // Serviço Guzzle
    protected $RequestService;
    private $appKey;
    private $appSecret;

    public function __construct()
    {
        
        // Inicializa o serviço de requisições HTTP
        $this->RequestService = new RequestService();
        
        // Carrega credenciais do TikTok a partir do config/tiktok.php
        $this->appKey = config('tiktok.app_key');
        $this->appSecret = config('tiktok.app_secret');

    }

    /**
     * Obtém o Access Token do TikTok.
     */
    public function getAccessToken($code)
    {
        // URL de endpoint para troca de código por token
        $url = "https://auth.tiktok-shops.com/api/v2/token/get" .
            "?app_key={$this->appKey}" .
            "&app_secret={$this->appSecret}" .
            "&auth_code={$code}" .
            "&grant_type=authorized_code";

        // Envia requisição
        $response = $this->RequestService->request('GET', $url);

        return $response;

    }
}