<?php

namespace App\Http\Controllers;

use App\Models\ClientDomain;
use App\Models\ClientIntegration;
use App\Models\ClientMeta;
use App\Models\LogsApi;
use App\Jobs\MetaDispatchRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\MetaApiService;
use App\Services\RequestService;

class MetaApiController extends Controller
{

    // Serviço Guzzle
    protected $RequestService;
    private $metaAppId;
    protected $metaService;
    protected $scopesWhatsApp  = 'whatsapp_business_management,whatsapp_business_messaging,business_management,pages_show_list';
    protected $scopesInstagram = 'instagram_manage_messages,instagram_basic,pages_show_list,pages_read_user_content,business_management,pages_messaging,pages_read_engagement';
    protected $scopesInstagramAuth2 = 'instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments,instagram_business_content_publish,instagram_business_manage_insights';
            
    // Carrega credenciais do Meta App a partir do config/meta.php
    public function __construct() {
        $this->metaService = new MetaApiService();
        $this->metaAppId = config('meta.client_id');
        $this->RequestService = new RequestService();
    }

    /**
     * Autorização de Webhook através da Meta.
     */
    public function authWebhooks(Request $request)
    {

        /**
         * Obtém parametros de verificação disparados
         * pela Meta, eles precisam ser iguais ao que
         * o sistema miCore espera
         */
        $data = $request->all();

        // Se forem compatíveis, retorna sucesso
        if(isset($data['hub_verify_token']) && $data['hub_verify_token'] === Config::get('meta.verify_token')){
            return response($data['hub_challenge'], 200);
        } else {
            return response('Invalid Verify Token', 403);
        }

    }  

    /**
     * Função responsavel por receber o Webhook da Meta (Facebook, WhatsApp, Instagram etc)
     */
    public function return(Request $request, $logOld = null)
    {
        Log::info(json_encode($request->all()));

        // Obtém dados
        $data = $request->all();

        // Dispara para a função que resolve
        $this->handle($data, $logOld);

        // Retorno Sucesso imediato para o Meta (202 Accepted)
        return response()->json([
            'status' => 'Accepted',
            'message' => 'Webhook recebido e será processado em background.'
        ], 202);
    }

    public function handle(array $data, $logOld = null)
    {

        /**
         * Salvamos em uma tabela interna no miCore
         * para debugar e garantir que o webhook foi 
         * recebido e salvo.
         */
        $logApi = LogsApi::create([
            'api' => 'Meta',
            'json' => json_encode($data),
        ]);

        // Se for um LogApi que está sendo reprocessado
        if($logOld){
            $logOld->new_log_id = $logApi->id;
            $logOld->save();
        }

        // Dispara para a função de encontrar o dominio a ser enviado o conteudo
        MetaDispatchRequest::dispatch($data, $logApi->id);

    }

    /**
     * Callback para receber autorização OAuth,
     * redireciona para a URL de origem com parametros,
     * recebidos da meta
     */
    public function callback(Request $request)
    {

        // Obtém dados
        $data = $request->all();

       /*  // Decodifica o state
        $data['decoded'] = json_decode(base64_decode($request->get('state')), true);

        // Obtém o tipo
        $type = $data['decoded']['type']; */

        /**
        * Troca o código de autorização (code) gerado na autenticação inicial do Meta
        */
        $response = $this->metaService->getAccessTokenInstagram($data['code'], $type);

        dd($response, $data, route('callbacks.meta.' . $type));

        /**
         * Se o código não é mais válido
         */
        if($response['status'] == 400){
            // Redireciona para aplicação
            return redirect()->away('http://' . $data['decoded']['origin'] . '/callbacks/meta/' . $type . '?code=' . $data['code'])->with([
                'message' => 'Código de autorização inválido.',
            ]);
        }

        /**
         * Caso tenha sucesso
         */
        if($response['success']){

            // Extrai dados
            $accessToken = $response['data']['access_token'];
            
            /**
             * Troca o token de acesso (access_token) gerado na autenticação inicial do Meta
             * por um token de acesso e refresh_token.
             *
             * Este método deve ser chamado logo após o usuário autorizar a aplicação no fluxo OAuth2.
             *
             */
            $responseLongToken = $this->metaService->getLongToken($accessToken);

            // Extrai dados
            $accessToken = $responseLongToken['data']['access_token'];

            /**
             * Caso tenha erro
             */
            if($responseLongToken['success'] == false){
                return redirect()->route('core.developers.test')->with([
                    'message' => 'Erro ao integrar conta.',
                ]);
            }

            // Obtém dados da conta
            $accountInformations = $this->metaService->me($accessToken);

            /**
             * Caso tenha erro
             */
            if($accountInformations['success'] == false){
                return redirect()->route('core.developers.test')->with([
                    'message' => 'Erro ao buscar dados da conta.',
                ]);
            }
            
            /**
             * Extrai dados
             */
            $accountId = $accountInformations['data']['id'];

            // Encontra o cliente que é dono do domínio
            $client = ClientDomain::where('domain', $data['decoded']['origin'])->first();

            /**
             * Lista de permissões
             */
            $scopesList = [
                'whatsapp'  => $this->scopesWhatsApp,
                'instagram' => $this->scopesInstagram,
            ];

            /**
             * Enviar para a conta miCore responsável
             */
            $clientIntegration = ClientIntegration::updateOrCreate([
                'external_account_id'   => $accountId,
                'client_id'             => $client->client_id,
                'provider'              => 'meta',
                'type'                  => $type,
            ], [
                'scopes'                => $scopesList[$type],
                'access_token'          => $accessToken,
            ]);

            // Redireciona para aplicação
            return redirect()->away('https://' . $data['decoded']['origin'] . '/callbacks/meta?integration_id=' . $clientIntegration->id);
            
        }

        /**
         * Caso tenha erro
         */
        return redirect()->away('https://' . $data['decoded']['origin'] . '/callbacks/meta?error=true');

    }



    /**
     * Gera a URL de autenticação OAuth2 para conexão com o Meta.
     * 
     * Esta URL é usada para redirecionar o usuário ao fluxo de login do Meta,
     * onde ele concede as permissões necessárias à aplicação.
     *
     * @return string URL completa de autenticação
     */
    public function OAuth2($type, $host)
    {

        // Valida o tipo de autenticação
        if($type != 'whatsapp' && $type != 'instagram'){
            return response()->json([
                'success' => false,
                'message' => 'Tipo de autenticação inválido',
            ], 400);
        }

        // Permissões
        if($type == 'whatsapp'){
            $scope = urlencode($this->scopesWhatsApp);
        } elseif ($type == 'instagram') {
            $scope = urlencode($this->scopesInstagram);
        }

        // Define o domínio da central
        $centralUrl = env('APP_URL') . '/callbacks/meta/' . $type;

        // URL de redirecionamento
        $redirectUri = urlencode($centralUrl);

        // Monta os dados do state
        $stateData = [
            'origin' => $host,
            'type'   => $type,
        ];

        // Codifica em base64 (evita problemas de URL)
        $state = urlencode(base64_encode(json_encode($stateData)));

        // Verifica se é o tipo de autenticação
        if($type == 'whatsapp'){

            // URL de autenticação
            $oauthUrl = "https://www.facebook.com/v20.0/dialog/oauth" .
                "?client_id={$this->metaAppId}" .
                "&redirect_uri={$redirectUri}" .
                "&scope={$scope}" .
                "&state={$state}";
            
        } elseif ($type == 'instagram') {

            Log::info('Rota gerada na autorização do Instagram:');
            Log::info(route('callbacks.meta.instagram'));

            $oauthUrl = "https://www.instagram.com/oauth/authorize?"
                . "force_reauth=true&client_id=" . Config::get('meta.app_instagram_id') . "&"
                . "redirect_uri=" . route('callbacks.meta.instagram') . "&"
                . "response_type=code&"
                . "scope={$this->scopesInstagramAuth2}"
                . "&state={$state}";

            Log::info('URL de autenticação gerada:');
            Log::info($oauthUrl);

        }


        // Retorna URL de autenticação
        return response()->json([
            'url' => $oauthUrl,
        ], 200);

    }

    /**
     * API em que um MiCore solicita os dados de um token
     * em que um dos usuários dele autorizou através do 
     * sistema de atendimento. 
     */
    public function token(Request $request, $id)
    {
        
        // Obtém host
        $host = $request->host;

        // Obtém o Token solicitado
        $token = ClientIntegration::find($id);

        // Verifica se o token foi encontrado
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token não encontrado',
            ], 404);
        }

        // Verifica se o token pertence ao mesmo host
        $domain = ClientDomain::where('domain', $host)->first();

        // Verifica se o token pertence ao mesmo host
        if (!$domain || $domain->client_id !== $token->client_id) {
            return response()->json([
                'success' => false,
                'message' => 'Resgate não autorizado',
            ], 404);
        }

        // Localiza o token e verifica a autorização
        return response()->json([
            'success' => true,
            'data' => $token->toArray(),
        ]);
    }

    /**
     * Recebe da conta do cliente quais serão os negócios
     * da meta que serão responsabilidade da central receber
     * as notificações.
     * 
     * A partir disso a central separará as notificações
     * e enviará para o MiCore responsável.
     */
    public function subscribed(Request $request)
    {
        
        // Obtém dados
        $data = $request->all();

        // Obtém cliente associado ao miCore através do Token dele
        $client = Client::where('token', $data['token_micore'])->first();

        // Obtém o Token solicitado
        ClientMeta::updateOrCreate([
            'client_id' => $client->id,
            'meta_id' => $data['waba_id'],
        ], [
            'status' => $data['status'],
        ]);

        // Localiza o token e verifica a autorização
        return response()->json([
            'success' => true,
            'message' => $data['status'] ? 'Ativou os números dessa conta' : 'Desativou os números dessa conta',
        ]);
    }

    /**
     * Desativa a conta do Meta
     */
    public function unsubscribed(Request $request)
    {
        
        // Obtém dados
        $data = $request->all();
        
        // Obtém o ID da página
        $pageId = $data['page_id'];

        // Envia requisição via RequestService
        $response = $this->RequestService->request(
            'DELETE',
            'https://graph.facebook.com/v20.0/' . $pageId . '/subscribed_apps',
            [
                'query' => [
                    'access_token'   => Config::get('meta.client_id') . '|' . Config::get('meta.client_secret'),
                ]
            ]
        );

        // Localiza o token e verifica a autorização
        return response()->json($response);
    }

}
