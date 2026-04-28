<?php

namespace App\Http\Controllers;

use App\Models\TenantDomain;
use App\Models\TenantIntegration;
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
        $tenant = $request->all()['tenant'];

        // Carrega o pacote atual do cliente.
        $plan = $tenant->plans()->where('status', true)->first();

        // Resposta padrão quando não há pacote ativo.
        if (!$plan) {
            return response()->json([
                'package' => null,
                'renovation' => 0,
            ], 200);
        }

        // Inclui módulos e benefícios no payload do pacote.
        $plan['modules'] = $plan->modules;
        $plan['benefits'] = $plan->benefits;

        // Verifica se já existe renovação pendente.
        $existsRenovation = $tenant->orders()
            ->where('type', 'Renovação')
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'package' => $plan,
            'order' => $tenant->plan?->orders()->orderBy('created_at', 'DESC')->first(),
            'cycle'   => $tenant->plan?->orders()->orderBy('created_at', 'DESC')->first()->subscription->cycles()->orderBy('created_at', 'DESC')->first(),
            'renovation' => $tenant->renovation(),
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
        $tenant = $request->all()['tenant'];

        // Obtém página e limite.
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        // Calcula o offset.
        $offset = ($page - 1) * $limit;

        // Busca pedidos ordenados do mais recente para o mais antigo.
        $orders = $tenant->orders()
                    ->where('status', '!=', 'draft')
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->skip($offset)
                    ->take($limit)
                    ->get();

        // verifica se existe mais registros depois
        $hasMore = $tenant->orders()
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
            $orderData['packageName'] = $order->plan->name;
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
        $tenant = $request->all()['tenant'];

        // Obtem o pedido selecionado
        $order = $tenant->orders()->where('id', $id)->first();

        $subscription = $order->subscription;

        $plan = $order->plan;

        $transactions = $order->transactions;

        // Resposta padrão quando não há pacote ativo.
        if (!$order) {
            return response()->json([
                'order' => null,
            ], 200);
        }

        return response()->json([
            'order'        => $order,
            'package'      => $plan,
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
        $tenant = $request->all()['tenant'];

        // Busca cartões do cliente do mais recente para o mais antigo.
        $cards = $tenant->cards()->orderBy('created_at', 'DESC')->get();

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
    
    /**
     * API em que um MiCore solicita os dados de um token
     * em que um dos usuários dele autorizou através do 
     * sistema de atendimento. 
     */
    public function token(Request $request, $id)
    {
        
        // Obtém host
        $host = $request->host;

        // Obtém o Token solicitado
        $token = TenantIntegration::find($id);

        // Verifica se o token foi encontrado
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token não encontrado',
            ], 404);
        }

        // Verifica se o token pertence ao mesmo host
        $domain = TenantDomain::where('domain', $host)->first();

        // Verifica se o token pertence ao mesmo host
        if (!$domain || $domain->tenant_id !== $token->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Resgate não autorizado',
            ], 404);
        }

        // Localiza o token e verifica a autorização
        return response()->json([
            'success' => true,
            'data' => $token->toArray(),
        ]);
    }
}
