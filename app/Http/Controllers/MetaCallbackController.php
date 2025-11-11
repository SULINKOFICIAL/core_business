<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MetaApiService;

class MetaCallbackController extends Controller
{

    protected $metaService;
            
    public function __construct() {
        $this->metaService = new MetaApiService();
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

        // Loga dados
        Log::info(json_encode($data));

        // Decodifica o state
        $data['decoded'] = json_decode(base64_decode($request->get('state')), true);

        /**
        * Troca o código de autorização (code) gerado na autenticação inicial do Meta
        */
        $response = $this->metaService->getAccessToken($data['code']);
    
        Log::info('Token de curto prazo');
        Log::info(json_encode($response));

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
            $response = $this->metaService->getLongToken($accessToken);

            Log::info('Token de longo prazo');
            Log::info(json_encode($response));

            /**
             * Caso tenha erro
             */
            if($response['success'] == false){
                return redirect()->route('core.developers.test')->with([
                    'message' => 'Erro ao integrar conta.',
                ]);
            }

            // Obtém dados da conta
            $accountInformations = $this->metaService->me($accessToken);


            Log::info('Informações da conta');
            Log::info(json_encode($accountInformations));

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

            /**
             * Enviar para a conta miCore responsável
             */
            dd($response);
            
        }

        // Redireciona para aplicação
        return redirect()->away('http://' . $data['decoded']['origin'] . '/callbacks/meta?code=' . $data['code']);

    }
}
