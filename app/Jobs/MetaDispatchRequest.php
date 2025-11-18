<?php

namespace App\Jobs;

use App\Models\ClientMeta;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Services\RequestService;
use Illuminate\Support\Facades\Log;

class MetaDispatchRequest implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $logApiId;
    protected $logApi;

    public function __construct($data, $logApiId)
    {
        $this->data = $data;
        $this->logApiId = $logApiId;
    }

    public function handle(RequestService $requestService): void
    {


        Log::info(json_encode($this->data));

        // Busca o logApi
        $this->logApi = LogsApi::find($this->logApiId);

        // Verifica qual o tipo da plataforma
        $platform = match($this->data['object']){
            'whatsapp_business_account' => 'whatsapp',
            'instagram'                 => 'instagram',
            'page'                      => 'facebook',
            'whatsapp_web'              => 'whatsapp_web',
        };

        // Pelo tipo da plataforma obtem o id da requisição
        $id = match($platform){
            'whatsapp'      => $this->data['entry'][0]['id'],
            'instagram'     => $this->data['entry'][0]['id'],
            'facebook'      => $this->data['entry'][0]['id'],
            'whatsapp_web'  => $this->data['number'],
        };

        // Obtem o cliente pelo id da meta
        $clientMeta = ClientMeta::where('meta_id', $id)->first();

        // Se não encontrar retorna erro
        if(!$clientMeta){
            $this->logApi->update([
                'status' => 'Erro',
            ]);
            return;
        }

        // Obtem os dominios do cliente
        $clientDomains = $clientMeta->client->domains;

        // Realiza a requisição
        $response = $requestService->request('POST', "{$clientDomains[0]->domain}/webhooks/meta", [
                        'json' => $this->data
                    ]);

        // Se a requisição foi processada atualiza o logs para concluido
        if($response['success'] && isset($response['data']['status']) && $response['data']['status'] == 'Accepted'){
            $this->logApi->update([
                'status' => 'Processado',
            ]);
        }
        
    }
}