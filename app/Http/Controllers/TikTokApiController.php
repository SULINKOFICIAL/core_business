<?php

namespace App\Http\Controllers;

use App\Models\TenantDomain;
use App\Models\TenantIntegration;
use App\Services\RequestService;
use App\Services\TikTokApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TikTokApiController extends Controller
{
    // Serviço Guzzle
    protected $RequestService;
    protected $appKey;
    protected $appSecret;
    protected $tiktokService;
    protected $scopes  = 'product,order,shop,customer_service';
            
    // Carrega credenciais do TikTok App a partir do config/tiktok.php
    public function __construct() {
        $this->tiktokService  = new TikTokApiService();
        $this->appKey         = config('tiktok.app_key');
        $this->appSecret      = config('tiktok.app_secret');
        $this->RequestService = new RequestService();
    }
    
    /**
     * Gera a URL de autenticação OAuth2 para conexão com o TikTok.
     * 
     * Esta URL é usada para redirecionar o usuário ao fluxo de login do TikTok,
     * onde ele concede as permissões necessárias à aplicação.
     *
     * @return string URL completa de autenticação
     */
    public function OAuth2($host)
    {
        // Define o domínio da central
        $centralUrl = env('APP_URL') . '/callbacks/tiktok';

        // URL de redirecionamento
        $redirectUri = urlencode($centralUrl);

        // Monta os dados do state
        $stateData = [
            'origin' => $host,
            'scope'  => $this->scopes,
        ];

        // Codifica em base64 (evita problemas de URL)
        $state = urlencode(base64_encode(json_encode($stateData)));

        // URL de autenticação
        $oauthUrl = "https://auth.tiktok-shops.com/oauth/authorize" .
            "?app_key={$this->appKey}" .
            "&response_type=code" .
            "&scope={$this->scopes}" .
            "&redirect_uri={$redirectUri}" .    
            "&state={$state}";

        // Retorna URL de autenticação
        return response()->json([
            'url' => $oauthUrl,
        ], 200);

    }

    /**
     * Callback de autenticação com o TikTok.
     */
    public function callback(Request $request)
    {
        // Obtem dados
        $data = $request->all();

        // Decodifica o state
        $data['state'] = json_decode(base64_decode($data['state']), true);

        /**
        * Troca o código de autorização (code) gerado na autenticação inicial do TikTok
        */
        $response = $this->tiktokService->getAccessToken($data['code']);

        /**
         * Caso tenha sucesso
         */
        if($response['success'] && !isset($response['data']['error'])){

            // Obtem o token de acesso
            $accessToken = $response['data']['data']['access_token'];

            // Obtem o token de refresh
            $refreshToken = $response['data']['data']['refresh_token'];

            // Obtem a expiração do token de acesso
            $tokenExpiresAt = Carbon::createFromTimestamp($response['data']['data']['access_token_expire_in']);

            // Obtem a expiração do token de refresh
            $refreshTokenExpiresAt = Carbon::createFromTimestamp($response['data']['data']['refresh_token_expire_in']);

            // Obtem o id da conta
            $accountId = $response['data']['data']['open_id'];

            // Encontra o cliente que é dono do domínio
            $tenant = TenantDomain::where('domain', $data['state']['origin'])->first();

            /**
             * Enviar para a conta miCore responsável
             */
            $clientIntegration = TenantIntegration::updateOrCreate([
                'external_account_id'      => $accountId,
                'tenant_id'                => $tenant->tenant_id,
                'provider'                 => 'tiktok',
                'type'                     => 'tiktok',
            ], [
                'scopes'                   => json_encode($response['data']['data']['granted_scopes']),
                'access_token'             => $accessToken,
                'token_expires_at'         => $tokenExpiresAt,
                'refresh_token'            => $refreshToken,
                'refresh_expires_at'       => $refreshTokenExpiresAt,
            ]);

            // Redireciona para aplicação
            return redirect()->away('https://' . $data['state']['origin'] . '/callbacks/tiktok?integration_id=' . $clientIntegration->id);
            
        }

        /**
         * Caso tenha erro
         */
        return redirect()->away('https://' . $data['state']['origin'] . '/callbacks/tiktok?error=true');

    }

    /**
     * Retorno do Webhook
     */
    public function return(Request $request)
    {
        // Obtem dados
        $data = $request->all();

        Log::info('TikTok Webhook', $data);

        // Retorno Sucesso imediato para o Tiktok (200 OK)
        return response()->json([
            'status' => 'Accepted',
            'message' => 'Webhook recebido e será processado em background.'
        ], 200);

    }
}
