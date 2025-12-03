<?php

namespace App\Http\Controllers;

use App\Jobs\MetaDispatchRequest;
use App\Models\LogsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppApiController extends Controller
{
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
        MetaDispatchRequest::dispatch($data, $logApi->id);

    }
}
