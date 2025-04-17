<?php

namespace App\Http\Controllers;

use App\Jobs\ChargeSubscriptions;
use App\Jobs\GenerateRenewalOrders;
use App\Models\ClientSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ERedeService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{

    // Gerencia Serviço eRede
    protected $eRedeService;
    protected $request;

    public function __construct(Request $request, ERedeService $eRedeService)
    {

        $this->request = $request;
        $this->eRedeService = $eRedeService;

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

        // Retorna com sucesso
        return redirect()->back()->with('success', count($expiredSubscriptions) . ' assinaturas atualizadas para Expirado.');

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generate()
    {

        // Chama Job que gera pedidos de renovação
        (new GenerateRenewalOrders())->handle();

        // Retorna com sucesso
        return redirect()->back()->with('success', 'Job de renovação executado diretamente.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function charge()
    {

        // Chama Job que cobra os pedidos de renovação
        app(ChargeSubscriptions::class)->handle();

        // Retorna com sucesso
        return redirect()->back()->with('success', 'Renovações cobradas.');

    }

}
