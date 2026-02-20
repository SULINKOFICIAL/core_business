<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ApisOrdersController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function orders(Request $request)
    {
        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $orders = $client->orders()->orderBy('created_at', 'DESC')->get();

        // Inicia Json
        $ordersJson = [];

        // Formata dados Json
        foreach ($orders as $order) {

            // Date formated
            $buy['id'] = $order->id;
            $buy['date_created'] = $order->created_at;
            $buy['date_paid'] = $order->paid_at;
            $buy['type'] = $order->type;
            $buy['amount'] = $order->total();
            $buy['method'] = $order->method;
            $buy['description'] = $order->description;
            $buy['status'] = $order->status;
            $buy['packageName'] = $order->package->name;
            $buy['transactions'] = $order->transactions->count();

            // Se for a atribuição de um pacote
            if ($buy['type'] == 'Pacote Trocado') {
                $buy['previousPackageName'] = $order->previousPackage->name;
            }

            // Obtém dados
            $ordersJson[] = $buy;
        }

        // Se o cliente tiver plano
        return response()->json($ordersJson, 200);
    }

    /**
     * Retorna o pedido em rascunho mais recente do cliente (se existir).
     * Obs.: Geralmente é o pedido em andamento.
     */
    public function orderDraft(Request $request)
    {
        // Extrai os dados
        $data = $request->all();

        // Extrai o cliente
        $client = $data['client'];

        // Busca o pedido em andamento
        $order = Order::where('client_id', $client->id)
            ->where('status', 'draft')
            ->orderBy('created_at', 'DESC')
            ->with(['items.configurations'])
            ->first();

        // Monta os itens com os dados relevantes para o front
        $items = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'name' => $item->item_name,
                'item_key' => $item->item_key,
                'billing_type' => $item->item_billing_type,
            ];
        });

        // Calcula subtotal e desconto aplicado
        $subtotalAmount = (float) $order->items()->sum('amount');
        $discountAmount = (float) ($order->coupon_discount_amount ?? 0);

        // Responde com o rascunho e os itens formatados
        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'current_step' => $order->current_step,
            'amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $order->total(),
            'currency' => $order->currency,
            'items' => $items,
        ], 200);
    }

    /**
     * Retorna as opções de uso (tiers) para módulos do pedido.
     */
    public function orderUsageOptions(Request $request)
    {
        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        // Busca o rascunho do cliente com itens e configurações
        $order = Order::where('client_id', $client->id)
            ->where('status', 'draft')
            ->with(['items.configurations'])
            ->first();

        // Inicia lista de módulos que exigem seleção de uso
        $usageModules = [];

        // Percorre itens do pedido e filtra apenas módulos com cobrança por uso
        foreach ($order->items as $item) {
            if ($item->type !== 'Módulo') {
                continue;
            }

            $module = Module::where('id', $item->item_key)
                ->where('pricing_type', 'Preço Por Uso')
                ->first();

            if (!$module) {
                continue;
            }

            // Carrega as faixas (tiers) do módulo ordenadas por limite
            $tiers = ModulePricingTier::where('module_id', $module->id)
                ->orderBy('usage_limit')
                ->get()
                ->map(function ($tier) {
                    return [
                        'usage_limit' => $tier->usage_limit,
                        'price' => (float) $tier->price,
                    ];
                })
                ->toArray();

            // Obtém uso já escolhido para o item (configuração ou snapshot)
            $usageConfig = null;
            $configItem = $item->configurations->firstWhere('key', 'usage');
            if ($configItem) {
                $usageConfig = $configItem->value;
            } elseif (is_array($item->pricing_model_snapshot ?? null) && isset($item->pricing_model_snapshot['usage'])) {
                $usageConfig = $item->pricing_model_snapshot['usage'];
            }

            $usageModules[] = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'tiers' => $tiers,
                'selected_usage' => $usageConfig,
            ];
        }

        return response()->json([
            'order_id' => $order->id,
            'modules' => $usageModules,
        ], 200);
    }

    public function order(Request $request, $id)
    {
        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = $data['client'];

        // Busca o pedido do cliente
        $order = Order::where('client_id', $client->id)->where('id', $id)->first();

        // Formata o pedido
        $orderJson['id'] = $order->id;
        $orderJson['date_created'] = $order->created_at;
        $orderJson['date_paid'] = $order->paid_at;
        $orderJson['type'] = $order->type;
        $orderJson['amount'] = $order->total();
        $orderJson['method'] = $order->method;
        $orderJson['description'] = $order->description;
        $orderJson['status'] = $order->status;
        $orderJson['packageName'] = $order->package->name;

        // Caso não encontre a conta do cliente
        if (!$client) {
            return response()->json('Pedido não encontrado', 404);
        }

        // Obtém transações do pedido
        $transactions = $order->transactions;

        // Insere o pedido formatado
        $transactionsJson = [];

        // Formata dados Json
        foreach ($transactions as $transaction) {
            $buy['id'] = $transaction->id;
            $buy['amount'] = $transaction->amount;
            $buy['method'] = $transaction->method;
            $buy['gateway'] = $transaction->gateway ? $transaction->gateway->name : null;
            $buy['date_created'] = $transaction->created_at;
            $buy['status'] = $transaction->status;

            $transactionsJson[] = $buy;
        }

        // Insere as transações no pedido
        $orderJson['transactions'] = $transactionsJson;

        return response()->json($orderJson, 200);
    }

    /**
     * Cria um pedido em rascunho (intenção de compra) com base nos módulos desejados.
     */
    public function orderUpdate(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Extrai cliente
        $client = $data['client'];

        // Cria ou atualiza o rascunho com os módulos enviados
        $order = $this->orderService->getOrderInProgress($client);

        // Realiza ação desejada
        $action = match ($data['action']) {
            'change_module' => $this->toggleModule($order, $data['value']),
            'step' => $this->updateStep($order, $data['value']),
            default => null,
        };

        // Retorna resposta
        return response()->json([
            'message' => $action['message'],
            'order' => $order
        ]);
    }

    /**
     * Atualiza o passo do pedido
     */
    private function updateStep($order, $step)
    {
        // Atualiza a etapa
        $order->current_step = $step;
        $order->save();

        return [
            'message' => 'Passo atualizado com sucesso',
            'order' => $order
        ];
    }

    /**
     * Adiciona ou remove o módulo
     */
    private function toggleModule($order, $moduleId)
    {
        // Busca dados do módulo
        $module = Module::find($moduleId);

        // Verifica se esse pedido já tem esse item
        $existingItem = OrderItem::where('order_id', $order->id)
            ->where('type', 'Módulo')
            ->where('item_key', $moduleId)
            ->first();

        // Se o módulo já existe, remove
        if ($existingItem) {
            $existingItem->delete();

            // Recalcula os totais
            $this->orderService->recalculateOrderTotals($order);

            return [
                'message' => 'Módulo removido com sucesso.',
                'action' => 'removed',
            ];
        }

        // Cria item de módulo no pedido
        OrderItem::create([
            'order_id' => $order->id,
            'type' => 'Módulo',
            'item_name' => $module->name,
            'item_key' => $module->id,
            'item_billing_type' => $module->pricing_type,
            'item_usage_quantity' => 1,
            'item_value' => $module->value,
            'amount' => $module->value,
            'payload' => json_encode($module->toArray()),
        ]);

        // Recalcula os totais
        $this->orderService->recalculateOrderTotals($order);

        return [
            'message' => 'Módulo adicionado com sucesso.',
            'action' => 'added',
        ];
    }
}
