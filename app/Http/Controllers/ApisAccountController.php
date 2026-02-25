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

        // Busca pedidos ordenados do mais recente para o mais antigo.
        $orders = $client->orders()->orderBy('created_at', 'DESC')->get();

        // Inicia lista de resposta.
        $ordersJson = [];

        // Formata cada pedido para o front.
        foreach ($orders as $order) {
            $orderData['id'] = $order->id;
            $orderData['date_created'] = $order->created_at;
            $orderData['date_paid'] = $order->paid_at;
            $orderData['type'] = $order->type;
            $orderData['amount'] = $order->total();
            $orderData['method'] = $order->method;
            $orderData['description'] = $order->description;
            $orderData['status'] = $order->status;
            $orderData['packageName'] = $order->package->name;
            $orderData['transactions'] = $order->transactions->count();

            // Inclui pacote anterior quando houver troca.
            if ($orderData['type'] === 'Pacote Trocado') {
                $orderData['previousPackageName'] = $order->previousPackage->name;
            }

            $ordersJson[] = $orderData;
        }

        return response()->json($ordersJson, 200);
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
