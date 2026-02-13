<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDomain;
use App\Models\ClientIntegration;
use App\Models\ClientMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaApiOnboardingController extends MetaApiController
{
    /**
     * Inicia o fluxo de Embedded Signup na central e retorna a URL da página fixa de onboarding.
     */
    public function startOnboarding(Request $request)
    {
        
        // Lê os dados enviados pelo miCore para iniciar o onboarding.
        $data = $request->all();

        $type = $data['type'] ?? 'whatsapp';
        $host = $data['host'] ?? null;

        Log::info($data);

        // Garante que o domínio realmente pertence ao cliente autenticado via token_micore.
        $domain = ClientDomain::where('domain', $host)->first();

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domínio não autorizado para este cliente.',
            ], 403);
        }

        // Gera um UUID único para identificar a sessão de onboarding.
        $signupSession = (string) Str::uuid();
        $expiresAt = now()->addMinutes(60);

        // Monta o state assinado para trafegar contexto do fluxo com validade.
        $statePayload = [
            'signup_session' => $signupSession,
            'client_id' => $data['client']->id,
            'host' => $host,
            'type' => $type,
            'exp' => $expiresAt->timestamp,
        ];

        // Assina o state para segurança e integridade dos dados.
        $signedState = Crypt::encryptString(json_encode($statePayload));

        // Persiste sessão temporária para validações futuras do fluxo embedded.
        Cache::put('meta_embedded_signup:' . $signupSession, [
            'client_id' => $data['client']->id,
            'host' => $host,
            'type' => $type,
        ], $expiresAt);

        Log::info('MetaApiOnboardingController:startOnboarding', [
            'signupSession' => $signupSession,
        ]);

        // Devolve a URL fixa da central onde o SDK da Meta será carregado.
        return response()->json([
            'success' => true,
            'url' => route('meta.embedded.onboarding', [
                'state' => $signedState,
            ]),
        ]);
    }

    /**
     * Renderiza a página fixa da central onde o SDK da Meta executa o Embedded Signup.
     */
    public function embeddedOnboarding(Request $request)
    {
        // Recebe o state assinado vindo da URL de onboarding.
        $signedState = $request->query('state');

        if (!$signedState) {
            abort(400, 'Parâmetro state não informado.');
        }

        // Descriptografa o state para validar os dados da sessão.
        $state = json_decode(Crypt::decryptString($signedState), true);

        if (now()->timestamp > (int) $state['exp']) {
            abort(410, 'State expirado.');
        }

        // Renderiza a página que executa o Embedded Signup no domínio fixo da central.
        return view('pages.integrations.meta_embedded_signup')->with([
            'signedState'       => $signedState,
            'signupSession'     => $state['signup_session'],
            'type'              => $state['type'],
            'host'              => $state['host'],
            'metaAppId'         => $this->metaAppId,
            'embeddedConfigId'  => config('meta.embedded_signup_config_id'),
            'graphVersion'      => config('meta.embedded_signup_graph_version', 'v24.0'),
        ]);
    }

    /**
     * Processa o payload bruto do postMessage do Embedded Signup e finaliza a integração.
     */
    public function embeddedOnboardingCallback(Request $request)
    {

        Log::info($request->all());

        // Obtém dados
        $data = $request->all();

        // Consolida validações iniciais do callback em um único ponto.
        $validationResult = $this->validateOnboardingCallback($data, false);
        if ($validationResult instanceof JsonResponse) {
            return $validationResult;
        }

        // Extrai dados
        $state      = $validationResult['state'];
        $session    = $validationResult['session'];
        $rawMessage = $validationResult['rawMessage'];
        $cacheKey   = $validationResult['cacheKey'];

        // Decodifica o payload
        $dataRaw = json_decode($rawMessage, true);

        // Verifica se é um evento de signup
        if (is_array($dataRaw) && isset($dataRaw['type']) && $dataRaw['type'] === 'WA_EMBEDDED_SIGNUP') {
            return $this->handleWaEmbeddedSignupEvent($dataRaw, $cacheKey, $session);
        }

        // Parseia a mensagem
        parse_str($rawMessage, $parsed);

        // Handle code event
        return $this->handleCodeEvent($parsed, $state, $session, $cacheKey);
    }

    /**
     * Executa as validações iniciais do callback e retorna os dados normalizados para processamento.
     */
    private function validateOnboardingCallback(array $data, $verifications = true)
    {
        // Extrai dados do payload
        $signedState    = $data['signed_state'] ?? null;
        $rawMessage     = $data['raw_message'] ?? null;
        $origin         = $data['origin'] ?? '';

        // Decifra o state
        $state = json_decode(Crypt::decryptString($signedState), true);

        // Recupera sessão temporária de onboarding no cache.
        $cacheKey = 'meta_embedded_signup:' . $state['signup_session'];
        $session = Cache::get($cacheKey);

        if($verifications){
                
            // Exige campos obrigatórios do payload bruto.
            if (!$signedState || !$rawMessage) {
                return response()->json([
                    'success' => false,
                    'status' => 'invalid_payload',
                    'message' => 'Payload inválido.',
                ], 422);
            }

            // Aceita apenas origem do domínio da Meta.
            if (!preg_match('/^https:\/\/([a-z0-9-]+\.)*facebook\.com$/i', $origin)) {
                return response()->json([
                    'success' => false,
                    'status' => 'invalid_origin',
                    'message' => 'Origem inválida.',
                ], 422);
            }

            // Valida estrutura mínima do state.
            if (!$state || !isset($state['signup_session'], $state['host'], $state['client_id'], $state['exp'])) {
                return response()->json([
                    'success' => false,
                    'status' => 'invalid_state',
                    'message' => 'State malformado.',
                ], 422);
            }

            // Bloqueia state expirado antes de qualquer operação.
            if (now()->timestamp > (int) $state['exp']) {
                return response()->json([
                    'success' => false,
                    'status' => 'expired_state',
                    'message' => 'State expirado.',
                    'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
                ], 410);
            }

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'status' => 'session_not_found',
                    'message' => 'Sessão de onboarding não encontrada.',
                    'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
                ], 404);
            }

            // Garante que a sessão corresponde ao mesmo cliente e host do state.
            if ((int) $session['client_id'] !== (int) $state['client_id'] || $session['host'] !== $state['host']) {
                return response()->json([
                    'success' => false,
                    'status' => 'session_mismatch',
                    'message' => 'Sessão inválida.',
                    'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
                ], 403);
            }

        }

        return [
            'state' => $state,
            'session' => $session,
            'rawMessage' => $rawMessage,
            'cacheKey' => $cacheKey,
        ];
    }

    /**
     * Processa evento WA_EMBEDDED_SIGNUP e persiste dados temporários da WABA na sessão.
     */
    private function handleWaEmbeddedSignupEvent(array $dataRaw, string $cacheKey, array $session)
    {

        /**
         * Registra a conta meta em nosso sistema.
         */
        $metaAccount = ClientMeta::updateOrCreate([
            'meta_id'   => $dataRaw['data']['waba_id'],
            'client_id' => $session['client_id'],
        ],[
            'status' => true,
        ]);

        // Extrai a sessão
        $temporary = explode(':', $cacheKey)[1];

        /**
         * Registra a integração (Número de telefone)
         */
        ClientIntegration::updateOrCreate([
            'temporary'             => $temporary,
        ],[
            'client_id'             => $session['client_id'],
            'external_account_id'   => $dataRaw['data']['phone_number_id'],
            'provider'              => 'meta',
            'client_provider_id'    => $metaAccount->id,
            'type'                  => 'whatsapp',
            'status'                => 'in_progress',
        ]);

        // Renova a sessão para aguardar o evento final com code.
        Cache::put($cacheKey, $session, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'status' => 'Integração associado com sucesso.',
        ]);
    }

    /**
     * Processa evento com code, realiza exchange e finaliza o onboarding com redirect.
     */
    private function handleCodeEvent(array $parsed, array $state, array $session, string $cacheKey)
    {

        /**
         * Realiza validações necessárias para manter a segurança
         * entre a troca do token.
         */
        $validationResult = $this->validateCodeEvent($parsed, $state, $session);
        if ($validationResult instanceof JsonResponse) {
            return $validationResult;
        }

        // Extrai cliente
        $client = $validationResult['client'];

        /**
         * Realiza a troca de um token
         * Troca o authorization code por um token de curta duração.
         */ 
        $response = $this->metaService->getAccessToken($parsed['code']);

        // Caso ocorra erro
        if (isset($response['data']['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'Erro ao obter access token.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 400);
        }

        // Extrai dados
        $response = $response['data'];

        // Gera token de longa duração para manter a integração estável.
        $responseLongToken = $this->metaService->getLongToken($response['access_token']);

        // Extrai o token de acesso
        $accessToken = $responseLongToken['data']['access_token'] ?? null;

        // Verifica se o token foi gerado
        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar token de longa duração.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 400);
        }

        // Resgata a integração que iniciou o processo de onboarding
        $integration = ClientIntegration::where('client_id', $client->id)->where('temporary', $state['signup_session'])->first();
        if (!$integration || !$integration->meta) {
            return response()->json([
                'success' => false,
                'status' => 'integration_not_found',
                'message' => 'Integração temporária não encontrada.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 404);
        }
        
        // Busca sua WABA ID
        $wabaId = $integration->meta->meta_id;

        // Busca a WABA autorizada para confirmar que o ID é válido.
        $accountInformations = $this->metaService->waba($wabaId, $accessToken);

        if (!isset($accountInformations['data']['id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados da WABA.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 400);
        }

        // Consulta escopos e expiração reais do token para persistência.
        $debug = $this->metaService->debugToken($accessToken);

        // Revoga integrações anteriores da mesma conta antes de ativar a nova.
        ClientIntegration::where('client_id', $client->id)
                            ->where('external_account_id', $integration->external_account_id)
                            ->where('id', '!=', $integration->id)   
                            ->update([
                                'status' => 'revoked',
                            ]);

        // Atualiza a integração com o token novo.
        $integration->status            = 'active';
        $integration->access_token      = $accessToken;
        $integration->scopes            = json_encode($debug['data']['data']['scopes'] ?? []);
        $integration->token_expires_at  = isset($debug['data']['expires_at']) ? date('Y-m-d H:i:s', $debug['data']['expires_at']) : null;
        $integration->save();

        // Solicita inscrição do app na WABA para recebimento de webhooks.
        $subscribeApp = $this->metaService->subscribeApp($wabaId, $accessToken);

        // Finalização bem-sucedida: invalida sessão temporária.
        Cache::forget($cacheKey);

        // Monta URL de redirecionamento
        $redirectUrl = 'https://' . $state['host'] . '/callbacks/meta/coexistence';

        // Monta query string
        $redirectQuery = http_build_query([
            'integration_id'      => $integration->id,
            'external_account_id' => $integration->external_account_id,
            'waba_id'             => $wabaId,
            'name'                => $accountInformations['data']['name'] ?? 'WhatsApp Business',
        ]);

        // Retorna resposta JSON com redirect URL
        return response()->json([
            'success'       => true,
            'status'        => 'code_received',
            'redirect_url'  => $redirectUrl . '?' . $redirectQuery,
            'statusMeta'    => $subscribeApp,
        ]);

    }

    /**
     * Valida pré-condições do evento com code e devolve o cliente para o exchange.
     */
    private function validateCodeEvent(array $parsed, array $state, array $session)
    {
        if (!isset($parsed['code'])) {
            return response()->json([
                'success' => true,
                'status' => 'ignored',
            ]);
        }

        if (isset($parsed['state']) && $parsed['state'] !== $state['signup_session']) {
            return response()->json([
                'success' => false,
                'status' => 'invalid_meta_state',
                'message' => 'State retornado pela Meta inválido.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 422);
        }

        // Busca o cliente alvo informado no state assinado.
        $client = Client::find($state['client_id']);
        if (!$client) {
            return response()->json([
                'success' => false,
                'status' => 'client_not_found',
                'message' => 'Cliente não encontrado.',
                'redirect_url' => 'https://' . $state['host'] . '/callbacks/meta?error=true',
            ], 404);
        }

        return [
            'client' => $client,
        ];
    }
}
