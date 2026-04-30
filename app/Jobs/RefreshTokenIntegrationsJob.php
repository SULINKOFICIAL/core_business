<?php

namespace App\Jobs;

use App\Models\TenantIntegration;
use App\Services\GuzzleService;
use App\Services\MetaApiService;
use App\Services\TikTokApiService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshTokenIntegrationsJob implements ShouldQueue
{
    use Queueable;

    protected $tenant;
    protected $guzzleService;
    protected $integrationId;

    /**
     * Instancia as dependências.
     */
    public function __construct($integrationId = null) {
        $this->integrationId = $integrationId;
        $this->guzzleService = new GuzzleService();
    }

    /**
     * Função responsável por atualizar o token de acesso das integrações cadastradas na central.
     */
    public function handle(): void
    {
        /**
         * Verifica se veio integrationId
         */
        if(!empty($this->integrationId)) {
            
            /**
             * Obtem a integração
             */
            $integrations = [TenantIntegration::find($this->integrationId)];

        } else {
            
            /**
             * Obtem as integrações com um dia para expirar o token
             */
            $integrations = TenantIntegration::where('status', 'active')->whereDate('token_expires_at', Carbon::tomorrow())->get();
        }

        // Verifica se existe integrações a expirarem
        if(!empty($integrations)) {

            /**
             * Faz looping nas integrações
             */
            foreach($integrations as $integration) {
                try {

                    // Obtem o tenantId
                    $this->tenant = $integration->tenant;

                    // Obtem a plataforma da integração
                    $platform = $integration->provider;

                    // Envia para a função de acordo
                    $response = match($platform) {
                        'meta' => $this->refreshMetaToken($integration),
                        // 'tiktok' => $this->refreshTikTokToken($integration),
                        default => [
                            'success' => false,
                            'message' => 'Plataforma não encontrada.',
                        ],
                    };

                    Log::info('Refresh token integration job:', [
                        'integration_id' => $integration->id,
                        'response' => $response,
                    ]);

                } catch (\Throwable $th) {

                    Log::info('Refresh token integration job:', [
                        'integration_id' => $integration->id,
                        'error' => $th->getMessage(),
                    ]);

                    continue;
                }
            }
            
        }
        
    }

    /**
     * Função responsável por atualizar o token de acesso da meta.
     */
    private function refreshMetaToken($integration)
    {
        try {

            // Obtem o objeto da api meta
            $metaApi = new MetaApiService();

            // Atualiza o token de acesso
            $response = $metaApi->getLongToken($integration->access_token);

            // Verifica se a requisição foi bem sucedida
            if(isset($response['success']) && isset($response['data']['access_token'])) {

                // Obtem o token de acesso
                $accessToken = $response['data']['access_token'];
                
                // Obtem a data de expiração
                $expiresAt = Carbon::now()->addDays(60);

                // Atualiza o token de acesso
                $integration->update([
                    'access_token' => $accessToken,
                    'token_expires_at' => $expiresAt
                ]);

                // Atualiza o model
                $integration->refresh();

                // Envia para o micore para atualizar o token
                $response = $this->guzzleService->request(
                    'post', 
                    'sistema/atualizar-token', 
                    $this->tenant, 
                    [
                        'integration_id' => $integration->id,
                    ],
                    [
                        'timeout' => 5,
                    ]
                );

                // Retorna a resposta da API
                return $response;

            }

            // Retorna erro
            return [
                'success' => false,
                'message' => 'Token não encontrado.',
            ];

        } catch (\Throwable $th) {
            
            Log::info('Refresh token integration job:', [
                'integration_id' => $integration->id,
                'error' => $th->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao atualizar token.',
            ];
        }
    }

    /**
     * Função responsável por atualizar o token de acesso da tiktok.
     */
    private function refreshTikTokToken($integration) {
        try {
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
