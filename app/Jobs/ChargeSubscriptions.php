<?php

namespace App\Jobs;

use App\Models\ClientSubscription;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Services\ERedeService;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ChargeSubscriptions implements ShouldQueue
{
    use Queueable;
    protected OrderService $service;
    protected ERedeService $eRedeService;

    /**
     * Create a new job instance.
     */
    public function __construct(OrderService $service, ERedeService $eRedeService)
    {
        $this->service = $service;
        $this->eRedeService = $eRedeService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Pega as assinaturas que vencem hoje
        $subscriptionsToPaid = ClientSubscription::whereDate('end_date', date('Y-m-d'))->where('status', 'Ativo')->get();

        // Cobra dos cartões dos clientes
        foreach ($subscriptionsToPaid as $subscription) {
            
            // Obtém o cliente
            $client = $subscription->client;

            // Obtém cartão principal do usuário
            $mainCard = $client->cards()->where('main', true)->first();

            // Se tiver um cartão cadastrado para renovação automática
            if($mainCard){
                
                // Obtém pedido gerado pela renovação
                $orderRenovation = Order::where('client_id', $client->id)
                                        ->where('type', 'Renovação')
                                        ->where('status', 'Pendente')
                                        ->first();

                // Gera transação para processar o pedido
                $transaction = OrderTransaction::create([
                    'order_id'   => $orderRenovation->id,
                    'amount'     => $orderRenovation->total(),
                    'method'     => 'Gateway',
                    'gateway_id' => 1,
                ]);

                $responseRede = $this->eRedeService->transaction($transaction, $mainCard);

                // Se foi pago atribui o pacote ao cliente
                if($responseRede['returnCode'] == '00'){

                    // Salta o brandTid referente a transação em questão.
                    $transaction->brand_tid     = $responseRede['brandTid'];
                    $transaction->brand_tid_at  = now();
                    $transaction->status        = 'Pago';
                    $transaction->response      = json_encode($responseRede);
                    $transaction->save();

                    // Retorna o cliente atualizado
                    $this->service->confirmPaymentOrder($orderRenovation);

                } else {

                    // Atualiza para pago
                    $transaction->status = 'Falhou';
                    $transaction->response = json_encode($responseRede);
                    $transaction->save();
                    
                }
                
            }

        }
    }
}
