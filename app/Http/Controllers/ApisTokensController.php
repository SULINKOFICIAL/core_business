<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDomain;
use App\Models\ClientIntegration;
use App\Models\ClientMeta;
use Illuminate\Http\Request;

class ApisTokensController extends Controller
{

    // Serviço Guzzle
    protected $RequestService;
    private $metaAppId;
    private $metaAppClientSecret;

    public function __construct()
    {
        // Carrega credenciais do Meta App a partir do config/meta.php
        $this->metaAppId = config('meta.client_id');
        $this->metaAppClientSecret = config('meta.client_secret');
    }

    /**
     * Gera a URL de autenticação OAuth2 para conexão com o Meta.
     * 
     * Esta URL é usada para redirecionar o usuário ao fluxo de login do Meta,
     * onde ele concede as permissões necessárias à aplicação.
     *
     * @return string URL completa de autenticação
     */
    public function url($host)
    {

        // Define o domínio da central
        $centralUrl = env('APP_URL') . '/callbacks/meta';

        // URL de redirecionamento
        $redirectUri = urlencode($centralUrl);

        // Permissões
        $scope = urlencode('whatsapp_business_management,whatsapp_business_messaging,business_management,pages_show_list');

        // Monta os dados do state
        $stateData = ['origin' => $host];

        // Codifica em base64 (evita problemas de URL)
        $state = urlencode(base64_encode(json_encode($stateData)));

        // URL de autenticação
        $oauthUrl = "https://www.facebook.com/v20.0/dialog/oauth?client_id={$this->metaAppId}&redirect_uri={$redirectUri}&scope={$scope}&state={$state}";

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
            'message' => $data['status'] ? 'Subscrição ativa' : 'Subscrição desativada',
        ]);
    }

}
