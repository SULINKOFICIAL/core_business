<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ClientSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Models\Order;
use App\Models\OrderTransaction;
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

        return match ($PagarMeDTO->type) {
            'charge.paid',
            'charge.antifraud_approved' => $this->handleChargePaid($PagarMeDTO),
            'charge.failed'             => $this->handleChargeFailed($PagarMeDTO),
            default => true
        };

    }

    public function handleChargePaid($data)
    {
        // Verifica se é a primeira transação
        if($data->charge->recurrency == 'first') {

            // Obtem o transação
            $transaction = OrderTransaction::where('pagarme_transaction_id', $data->charge->id)->orderBy('id', 'DESC')->first();

            // Verifica se existe o item
            if($transaction) {

                // Atualiza as informações da transação
                $transaction->update([
                    'recurrency'   => $data->charge->recurrency,
                    'status'       => $data->charge->status,
                    'paid_at'      => $data->charge->paidAt,
                    'amount'       => $data->charge->paidAmount / 100,
                    'currency'     => $data->charge->currency,
                    'method'       => $data->invoice->method,
                ]);

                $order = $transaction->subscription->order;

                $order->update([
                    'status'      => $data->charge->status,
                    'currency'    => $data->charge->currency,
                    'paid_at'     => $data->charge->paidAt,
                    'method'      => $data->invoice->method,
                ]);

            }

        } else {

            // Obtem o cliente
            $client = Client::where('pagarme_customer_id', $data->customer->id)->first();

            if($client) {

                // Obtem o ultimo pedido desse cliente
                $lastOrder = Order::where('client_id', $client->id)->orderBy('id', 'DESC')->first();

                // Se existir um pedido
                if($lastOrder) {

                    // Replica o pedido
                    $newOrder = $lastOrder->replicate();

                    // Atualiza com as novas informações da PagarMe
                    $newOrder->fill([
                        'status'      => $data->charge->status,
                        'currency'    => $data->charge->currency,
                        'paid_at'     => $data->charge->paidAt,
                        'method'      => $data->invoice->method,
                    ]);

                    // Salva
                    $newOrder->save();

                    // Faz looping em todos os itens do pedido
                    foreach ($lastOrder->items as $item) {

                        // Replica
                        $newItem = $item->replicate();
                    
                        // Atualiza com o novo id
                        $newItem->order_id = $newOrder->id;
                    
                        // Salva
                        $newItem->save();
                    }

                    // Obtem a assinatura do pedido
                    $lastSubscription = $lastOrder->subscription;

                    // Replica a assinatura
                    $newSubscription = $lastSubscription->replicate();

                    // Atualiza as informações da assinatura
                    $newSubscription->fill([
                        'order_id'                => $newOrder->id,
                        'pagarme_subscription_id' => $data->invoice->subscriptionId,
                        'pagarme_card_id'         => $data->transaction->card->id,
                        'currency'                => $data->charge->currency,
                    ]);

                    // Salva
                    $newSubscription->save();

                    // Cria uma nova transação com as informações da PagarMe
                    $transaction = OrderTransaction::create([
                        'subscription_id'        => $newSubscription->id,
                        'pagarme_transaction_id' => $data->charge->id,
                        'gateway_code'           => $data->transaction->gatewayId,
                        'status'                 => $data->charge->status,
                        'amount'                 => $data->charge->paidAmount / 100,
                        'currency'               => $data->charge->currency,
                        'recurrency'             => $data->charge->recurrency,
                        'paid_at'                => $data->charge->paidAt,
                        'method'                 => $data->invoice->method,
                        'response'               => json_encode($data),
                    ]);

                }

            }

        }

        return true;

    }

    public function handleChargeFailed($data)
    {
        
    }
}