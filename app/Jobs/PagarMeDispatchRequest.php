<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Models\Order;
use App\Models\OrderSubscription;
use App\Models\OrderTransaction;
use App\Services\PagarMeResponseService;
use App\Services\PagarMeService;
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
        $pagarMeDTO = $pagarMeResponseService->process($this->requestData);

        /**
         * Se for Webhook com lógica de duplicidade
         * Retorna
         */
        if(!$pagarMeDTO) {
            return true;
        }

        return match ($pagarMeDTO->type) {
            'charge.created',
            'invoice.created'
                => $this->chargeCreated($pagarMeDTO),
            'charge.paid',
            'charge.antifraud_approved', 
            'charge.payment_failed',
            'invoice.paid',
            'invoice.payment_failed'
                => $this->chargeUpdated($pagarMeDTO),
            default => true
        };

    }

    public function chargeCreated($data)
    {
        // Verifica se existe uma transação com esse id
        $transaction = OrderTransaction::where('pagarme_transaction_id', $data->charge->id)->first();

        // Se existir uma transação
        if(!$transaction) {

            // Obtem o cliente
            $client = Client::where('pagarme_customer_id', $data->customer->id)->first();

            // Obtem o ultimo pedido do cliente
            $lastOrder = Order::where('client_id', $client->id)->orderBy('id', 'DESC')->first();

            // Se existir um pedido
            if($lastOrder) {

                // Replica o pedido
                $newOrder = $lastOrder->replicate();

                // Atualiza com as novas informações da PagarMe
                $newOrder->fill([
                    'status'      => $data->charge->status,
                    'currency'    => $data->charge->currency,
                    'paid_at'     => null,
                    'method'      => null,
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

                // Obtem a assinatura
                $subscription = (new PagarMeService())->findSubscription($data->invoice->subscriptionId);

                // Se view alguma assinatura na requisição
                if(isset($subscription) && isset($subscription['id'])) {

                    // Cria uma nova assinatura
                    $createSubscription = OrderSubscription::create([
                        'order_id'                => $newOrder->id,
                        'pagarme_subscription_id' => $subscription['id'],
                        'pagarme_card_id'         => $subscription['card']['id'],
                        'interval'                => $subscription['interval'],
                        'payment_method'          => $subscription['payment_method'],
                        'currency'                => $subscription['currency'],
                        'installments'            => $subscription['installments'],
                        'status'                  => $subscription['status'],
                        'billing_at'              => $subscription['current_cycle']['billing_at'],
                        'next_billing_at'         => $subscription['next_billing_at'],
                    ]);

                    // Cria a transação
                    OrderTransaction::create([
                        'subscription_id'        => $createSubscription->id,
                        'pagarme_transaction_id' => $data->charge->id,
                        'gateway_code'           => $data->transaction->gatewayId,
                        'status'                 => $data->charge->status,
                        'currency'               => $data->charge->currency,
                        'recurrency'             => $data->charge->recurrency,
                        'response'               => json_encode($data),
                    ]);

                }
                
            }

        }

    }

    public function chargeUpdated($data)
    {
        // Obtem o transação
        $transaction = OrderTransaction::where('pagarme_transaction_id', $data->charge->id)->orderBy('id', 'DESC')->first();

        // Verifica se existe o item
        if($transaction) {

            // Atualiza as informações da transação
            $transaction->update([
                'recurrency'   => $data->charge->recurrency,
                'status'       => $data->charge->status,
                'paid_at'      => $data->charge->paidAt ?? null,
                'amount'       => $data->charge->paidAmount / 100 ?? 0,
                'currency'     => $data->charge->currency,
                'method'       => $data->invoice->method,
            ]);

            $order = $transaction->subscription->order;

            $order->update([
                'status'      => $data->charge->status,
                'currency'    => $data->charge->currency,
                'paid_at'     => $data->charge->paidAt ?? null,
                'method'      => $data->invoice->method,
                'pagarme_message'   => $data->transaction->acquirer->message,
            ]);

        }

        return true;

    }
}