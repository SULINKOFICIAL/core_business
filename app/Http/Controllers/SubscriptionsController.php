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
    public function renew($id)
    {
        // Obtém o pedido referente a essa renovação
        $order = Order::find($id);

        // Verifica se o pedido é válido
        if (!$order || $order->status != 'Pendente' || $order->type != 'Renovação') {
            return redirect()
                ->route('clients.show', $order->client_id ?? null)
                ->with('message', 'Esse pedido não está como pendente ou não é uma renovação.');
        }

        // Obtém a assinatura do cliente relacionada ao pedido
        $subscription = ClientSubscription::where('client_id', $order->client_id)
                                            ->where('package_id', $order->key_id)
                                            ->orderBy('end_date', 'desc')
                                            ->first();

        // Caso não encontre a assinatura do usuário
        if (!$subscription) {
            return redirect()
                ->route('clients.show', $order->client_id)
                ->with('message', 'Nenhuma assinatura encontrada para renovação.');
        }

        // Definir a nova data de início e término da assinatura
        $startDate = $subscription->end_date->copy();
        $endDate = $startDate->copy()->addMonth();

        // Criar nova assinatura para o cliente
        ClientSubscription::create([
            'client_id'  => $order->client_id,
            'package_id' => $order->key_id,
            'order_id'   => $order->id,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => 'Ativo',
        ]);

        // Da 100% de desconto já que foi liberação manual
        OrderItem::create([
            'order_id'   => $order->id,
            'type'       => 'Desconto',
            'action'     => 'Desconto por liberação manual',
            'item_value' => -$order->total(),
        ]);

        // Atualiza o status do pedido para concluído
        $order->update([
            'description' => 'Liberação manual.',
            'description' => 'Liberação manual.',
            'status' => 'Pago',
        ]);

        return redirect()
                ->route('clients.show', $order->client_id)
                ->with('success', 'Assinatura renovada com sucesso!');
    }

    public function expired()
    {
        // Obtém todas as assinaturas que estão ativas e já passaram do prazo de validade
        $expiredSubscriptions = ClientSubscription::where('status', 'Ativo')
                                    ->where('end_date', '<', Carbon::now())
                                    ->get();

        // Verifica se há assinaturas vencidas
        if ($expiredSubscriptions->isEmpty()) {
            return redirect()
                ->back()
                ->with('success', 'Nenhuma assinatura vencida encontrada');
        }

        // Atualiza todas as assinaturas vencidas para "Expirado"
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'Expirado']);
        }

        return redirect()
                ->back()
                ->with('success', count($expiredSubscriptions) . ' assinaturas atualizadas para Expirado.');

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
        $subscriptions = ClientSubscription::where('status', 'Ativo')
                                ->where('end_date', '<=', Carbon::now()->addDays(5))
                                ->get();
                                
        // Para cada assinatura
        foreach ($subscriptions as $subscription) {

            // Cria um novo pedido para o cliente
            $client = $subscription->client;
            $package = $client->package;

            // Verifica se o cliente já tem um pedido de renovação neste mês
            $orderExists = Order::where('client_id', $client->id)
                                ->where('type', 'Renovação')
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year)
                                ->exists();
            
            // Se a solicitação de renovação ainda não foi gerada
            if (!$orderExists) {

                // Cria um novo pedido de renovação se não existir um no mês atual
                $order = Order::create([
                    'client_id'  => $client->id,
                    'key_id'     => $package->id,
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

        }

        return redirect()->back()->with(['message' => 'Assinaturas Geradas']);

    }

}
