<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ClientSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateRenewalOrders implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Agenda a geração automática de faturas para clientes 
     * cujos sistemas expiram em até 5 dias.
     * 
     * Este job é executado diariamente às 04:00 da manhã 
     * e garante que não ocorra sobreposição de execuções.
     */
    public function handle(): void
    {

        // Obtém assinaturas que irão vencer em 5 dias.
        $subscriptions = ClientSubscription::where('status', 'Ativo')
                                            ->whereDate('end_date', Carbon::now()->addDays(5)->toDateString())
                                            ->get();

        // Loop
        foreach ($subscriptions as $subscription) {

            // Obtém dados do cliente
            $client = $subscription->client;

            // Se o cliente tiver um pacote atribuido
            if(isset($client->package)){
                
                // Obtém dados do pacote
                $package = $client->package;

                // Se não for um pacote gratuito
                if(!$package->free){

                    // Verifica se já não foi gerado o pedido de renovação desse mes
                    $orderExists = Order::where('client_id', $client->id)
                                        ->where('type', 'Renovação')
                                        ->whereMonth('created_at', Carbon::now()->month)
                                        ->whereYear('created_at', Carbon::now()->year)
                                        ->exists();
        
                    // Caso ainda não exista
                    if (!$orderExists) {
        
                        // Cria pedido
                        $order = Order::create([
                            'client_id'  => $client->id,
                            'key_id'     => $package->id,
                            'status'     => 'Pendente',
                            'type'       => 'Renovação',
                        ]);
        
                        // Cria item que representa renovação
                        OrderItem::create([
                            'order_id'   => $order->id,
                            'item_type'  => 'package',
                            'action'     => 'Renovação',
                            'item_code'  => (string) $package->id,
                            'item_name_snapshot' => $package->name,
                            'quantity'   => 1,
                            'unit_price_snapshot' => $package->value,
                            'subtotal_amount' => $package->value,
                            // Legacy compatibility
                            'amount'     => $package->value,
                            'type'       => 'Pacote',
                            'item_name'  => $package->name,
                            'item_key'   => $package->id,
                            'item_value' => $package->value,
                        ]);
                    }

                }
    
            }

        }
    }
}
