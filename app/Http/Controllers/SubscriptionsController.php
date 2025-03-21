<?php

namespace App\Http\Controllers;

use App\Models\ClientSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, ClientSubscription $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generate()
    {

        // Verifica todas as assinaturas próximas de vencer
        $subscriptions = ClientSubscription::where('status', 'Ativa')
                                ->where('end_date', '<=', Carbon::now()->addDays(5))
                                ->get();
                                
        // Para cada assinatura
        foreach ($subscriptions as $subscription) {

            // Cria um novo pedido para o cliente
            $client = $subscription->client;
            $package = $client->package;

            // Verifica se o cliente já possui um pedido para renovação ou upgrade
            $order = Order::create([
                'client_id'  => $client->id,
                'key_id'     => $package->id,
                'order_date' => Carbon::now(),
                'status'     => 'Pendente',
                'type'       => 'Renovação',
            ]);

            // Verifica se o cliente já possui um pedido para renovação ou upgrade
            OrderItem::create([
                'order_id'   => $order->id,
                'amount'     => $package->value,
                'order_id'   => $order->id,
                'type'       => 'Pacote',
                'action'     => 'Renovação',
                'quantity'   => 1,
                'item_value' => $package->value,
            ]);

        }
       
        // PRECISA FAZER O PROCEDIMENTO PARA REPOIS QUE PAGA, GERAR A NOVA ASSINATURA COM DIAS ADICIONAIS
        // PRECISA FAZER O PROCEDIMENTO PARA REPOIS QUE PAGA, GERAR A NOVA ASSINATURA COM DIAS ADICIONAIS
        // PRECISA FAZER O PROCEDIMENTO PARA REPOIS QUE PAGA, GERAR A NOVA ASSINATURA COM DIAS ADICIONAIS

        // VERIFICAR SE CONSIGO APROVEITAR O MESMO PAYMENT DA API PARA COMPRA DE NOVOS PACOTES
        // VERIFICAR SE CONSIGO APROVEITAR O MESMO PAYMENT DA API PARA COMPRA DE NOVOS PACOTES
        // VERIFICAR SE CONSIGO APROVEITAR O MESMO PAYMENT DA API PARA COMPRA DE NOVOS PACOTES

        return 'Assinaturas Geradas.';

    }

}
