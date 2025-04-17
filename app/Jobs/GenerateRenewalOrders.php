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
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // Verifica todas as assinaturas próximas de vencer
        $subscriptions = ClientSubscription::where('status', 'Ativo')
                                            ->whereDate('end_date', Carbon::now()->addDays(5)->toDateString())
                                            ->get();

        foreach ($subscriptions as $subscription) {
            $client = $subscription->client;
            $package = $client->package;

            $orderExists = Order::where('client_id', $client->id)
                                ->where('type', 'Renovação')
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year)
                                ->exists();

            if (!$orderExists) {
                $order = Order::create([
                    'client_id'  => $client->id,
                    'key_id'     => $package->id,
                    'status'     => 'Pendente',
                    'type'       => 'Renovação',
                ]);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'amount'     => $package->value,
                    'type'       => 'Pacote',
                    'action'     => 'Renovação',
                    'quantity'   => 1,
                    'item_value' => $package->value,
                ]);
            }
        }
    }
}
