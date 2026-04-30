<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\TenantMeta;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Services\GuzzleService;
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

    public function handle(GuzzleService $guzzleService): void
    {

        // Busca o logApi
        $this->logApi = LogsApi::find($this->logApiId);
        if (!$this->logApi) {
            return;
        }

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
            'whatsapp_web'  => $this->data['tenant_id'],
        };

        if(in_array($platform, ['whatsapp', 'instagram', 'facebook'])){

            // Obtem o cliente pelo id da meta
            $clientMeta = TenantMeta::where('meta_id', $id)->first();

            // Se não encontrar retorna erro
            if(!$clientMeta){
                $this->logApi->update([
                    'status' => 'Erro',
                ]);
                return;
            }

            // Salva cliente vinculado ao log
            $this->logApi->update([
                'tenant_id' => $clientMeta->tenant_id,
            ]);

            // Obtem os dominios do cliente
            $clientDomains = $clientMeta->tenant->domains;
            if ($clientDomains->isEmpty()) {
                $this->logApi->update([
                    'status' => 'Erro',
                ]);
                return;
            }

            $tenant = $clientMeta->tenant;

            $url = "meta";

        } elseif ($platform == 'whatsapp_web') {

            // Obtem o cliente
            $tenant = Tenant::find($id);
            if (!$tenant) {
                $this->logApi->update([
                    'status' => 'Erro',
                ]);
                return;
            }

            // Salva cliente vinculado ao log
            $this->logApi->update([
                'tenant_id' => $tenant->id,
            ]);

            // Obtem os dominios do cliente
            $clientDomains = $tenant->domains;
            if ($clientDomains->isEmpty()) {
                $this->logApi->update([
                    'status' => 'Erro',
                ]);
                return;
            }

            $url = "whatsapp/{$this->data['route']}";

        }

        // O tenant sempre responde apenas com o aceite do disparo.
        $response = $guzzleService->request('POST', $url, $tenant, $this->data, [], 'webhooks');

        // Se a requisição foi processada atualiza o logs para concluido
        if($response['success'] && isset($response['data']['status']) && $response['data']['status'] == 'Accepted'){
            $this->logApi->update([
                'status' => 'Processado',
                'dispatched_at' => now(),
            ]);
        } else {
            $this->logApi->update([
                'status' => 'Erro',
            ]);
        }
        
    }
}
