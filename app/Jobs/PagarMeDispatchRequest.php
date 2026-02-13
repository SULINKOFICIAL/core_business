<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ClientSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Services\PagarMeResponseService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PagarMeDispatchRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    protected $logApiId;
    protected $logApi;

    // Número máximo de tentativas
    public $tries = 1;

    // Tempo de espera entre as tentativas (segundos)
    public $backoff = 10;

    // Tempo máximo (segundos) que o Job pode executar
    public function timeout()
    {
        return 60;
    }
    
    public function __construct(array $requestData, $logApiId = null)
    {
        $this->requestData = $requestData;
        $this->logApiId = $logApiId;

        // Busca o logApi
        $this->logApi = LogsApi::find($this->logApiId);
    }

    public function handle(PagarMeResponseService $pagarMeResponseService)
    {

        /**
         * DTO Formatado da PagarMe
         */
        $PagarMeDTO = $pagarMeResponseService->process($this->requestData);

        match ($PagarMeDTO->type) {
            'charge.paid' => $this->handleChargePaid($PagarMeDTO),
            'charge.failed' => $this->handleChargeFailed($PagarMeDTO),
        };

    }

    public function handleChargePaid($data)
    {
        // Obtem o cliente
        $client = Client::where('pagarme_customer_id', $data->customer->id)->first();

        // Obtem o cartão que o cliente usou para pagar
        $card = ClientCard::where('pagarme_card_id', $data->transaction->card->id)->first();

        // Obtem o pedido criado atraves 
        
        dd($data);

    }

    public function handleChargeFailed($data)
    {
        
    }
}