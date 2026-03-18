<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApisAccountController extends Controller
{
    /**
     * Retorna o pedido atual do cliente e informações de renovação.
     * Também informa se já existe um pedido de renovação pendente.
     */
    public function order(Request $request)
    {
        // Obtém cliente já anexado pelo middleware.
        $client = $request->all()['client'];

        // Carrega o pacote atual do cliente.
        $package = $client->packages()->where('status', true)->first();

        // Resposta padrão quando não há pacote ativo.
        if (!$package) {
            return response()->json([
                'package' => null,
                'renovation' => 0,
            ], 200);
        }

        // Inclui módulos no payload do pacote.
        $package['modules'] = $package->modules;

        // Verifica se já existe renovação pendente.
        $existsRenovation = $client->orders()
            ->where('type', 'Renovação')
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'package' => $package,
            'order' => $client->package?->orders()->orderBy('created_at', 'DESC')->first(),
            'cycle'   => $client->package?->orders()->orderBy('created_at', 'DESC')->first()->subscription->cycles()->orderBy('created_at', 'DESC')->first(),
            'renovation' => $client->renovation(),
            'existsOrder' => $existsRenovation,
        ], 200);
    }
    
    /**
     * Retorna o histórico de pedidos do cliente em formato simplificado.
     * Inclui dados de pagamento, status e quantidade de transações.
     */
    public function orders(Request $request)
    {
        // Obtém cliente já anexado pelo middleware.
        $client = $request->all()['client'];

        // Obtém página e limite.
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        // Calcula o offset.
        $offset = ($page - 1) * $limit;

        // Busca pedidos ordenados do mais recente para o mais antigo.
        $orders = $client->orders()
                    ->where('status', '!=', 'draft')
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->skip($offset)
                    ->take($limit)
                    ->get();

        // verifica se existe mais registros depois
        $hasMore = $client->orders()
                            ->skip($offset + $limit)
                            ->limit(10)
                            ->exists();

        // Inicia lista de resposta.
        $ordersJson = [];

        // Formata cada pedido para o front.
        foreach ($orders as $order) {
            $orderData['id'] = $order->id;
            $orderData['date_created'] = $order->created_at;
            $orderData['date_paid'] = $order->paid_at;
            $orderData['date_end'] = $order->end_date;
            $orderData['type'] = $order->type;
            $orderData['amount'] = $order->total_amount;
            $orderData['currency'] = $order->currency;
            $orderData['method'] = $order->method;
            $orderData['status'] = $order->status;
            $orderData['packageName'] = $order->package->name;
            $orderData['transactions'] = $order->transactions->count();

            $ordersJson[] = $orderData;
        }

        return response()->json([
            'data' => $ordersJson,
            'hasMore' => $hasMore
        ], 200);
    }

    /**
     * Retorna o pedido selecionado do cliente
     */
    public function invoice(Request $request, $id)
    {
        // Obtém cliente já anexado pelo middleware.
        $client = $request->all()['client'];

        // Obtem o pedido selecionado
        $order = $client->orders()->where('id', $id)->first();

        $subscription = $order->subscription;

        $package = $order->package;

        $transactions = $order->transactions;

        // Resposta padrão quando não há pacote ativo.
        if (!$order) {
            return response()->json([
                'order' => null,
            ], 200);
        }

        return response()->json([
            'order'        => $order,
            'package'      => $package,
            'subscription' => $subscription,
            'transactions' => $transactions,
        ], 200);
    }

    /**
     * Retorna os cartões salvos do cliente com dados mascarados.
     * Mantém apenas os campos necessários para seleção no checkout.
     */
    public function cards(Request $request)
    {
        // Obtém cliente já anexado pelo middleware.
        $client = $request->all()['client'];

        // Busca cartões do cliente do mais recente para o mais antigo.
        $cards = $client->cards()->orderBy('created_at', 'DESC')->get();

        // Inicia lista de resposta.
        $cardsJson = [];

        // Formata cartões com número mascarado.
        foreach ($cards as $card) {
            $cardData['id'] = $card->id;
            $cardData['main'] = $card->main;
            $cardData['name'] = $card->name;
            $cardData['number'] = '**** **** **** ' . substr($card->number, -4);
            $cardData['expiration'] = str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) . '/' . $card->expiration_year;
            $cardsJson[] = $cardData;
        }

        return response()->json($cardsJson, 200);
    }
}
