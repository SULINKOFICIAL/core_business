<?php

namespace App\Http\Controllers;

use App\Jobs\MetaDispatchRequest;
use App\Models\Client;
use App\Models\ClientDomain;
use App\Models\ClientIntegration;
use App\Models\LogsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\MetaApiService;

class MetaApiController extends Controller
{

    protected $metaService;
            
    public function __construct() {
        $this->metaService = new MetaApiService();
    }


    /**
     * Autorização de Webhook através da Meta.
     */
    public function token(Request $request)
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
        MetaDispatchRequest::dispatch($data, $logApi->id)->onQueue('whatsapp');

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

        // Decodifica o state
        $data['decoded'] = json_decode(base64_decode($request->get('state')), true);

        /**
        * Troca o código de autorização (code) gerado na autenticação inicial do Meta
        */
        $response = $this->metaService->getAccessToken($data['code']);

        /**
         * Se o código não é mais válido
         */
        if($response['status'] == 400){
            // Redireciona para aplicação
            return redirect()->away('http://' . $data['decoded']['origin'] . '/callbacks/meta?code=' . $data['code'])->with([
                'message' => 'Código de autorização inválido.',
            ]);
        }

        /**
         * Caso tenha sucesso
         */
        if($response['success']){

            // Extrai dados
            $accessToken = $response['data']['access_token'];
            $expiresIn = $response['data']['expires_in'];
            
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
            $expiresIn = $responseLongToken['data']['expires_in'];

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

            // Calcula a expiração
            $expiresAt = now()->addSeconds($expiresIn);

            // Encontra o cliente que é dono do domínio
            $client = ClientDomain::where('domain', $data['decoded']['origin'])->first();

            /**
             * Enviar para a conta miCore responsável
             */
            $clientIntegration = ClientIntegration::updateOrCreate([
                'external_account_id'   => $accountId,
                'client_id'             => $client->client_id,
            ], [
                'provider'              => 'meta',
                'access_token'          => $accessToken,
                'refresh_token'         => $responseLongToken['data']['access_token'],
                'token_expires_at'      => $expiresAt,
            ]);

            // Redireciona para aplicação
            return redirect()->away('https://' . $data['decoded']['origin'] . '/callbacks/meta?integration_id=' . $clientIntegration->id);
            
        }

        /**
         * Caso tenha erro
         */
        return redirect()->away('http://' . $data['decoded']['origin'] . '/callbacks/meta?error=true');

    }
}
