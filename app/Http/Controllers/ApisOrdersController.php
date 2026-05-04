<?php

namespace App\Http\Controllers;

use App\Models\TenantPlanItem;
use App\Models\AdditionalStorage;
use App\Models\AdditionalUser;
use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Models\Order;
use App\Models\Package;
use App\Models\TenantPlanItemConfiguration;
use App\Services\OrderService;
use App\Services\PagarMeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApisOrdersController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    /**
     * Retorna o pedido em rascunho mais recente do cliente (se existir).
     * Obs.: Geralmente é o pedido em andamento.
     */
    public function draft(Request $request)
    {
        // Obtem dados
        $data = $request->all();

        // Obtem cliente
        $tenant = $data['tenant'];

        // Obtem o pacote do cliente
        $plan = $this->orderService->getPlanInProgress($tenant);

        // Busca o pedido em andamento
        $order = $this->orderService->getOrderInProgress($tenant, $plan);

        // Monta os itens com origem (pacote) para o resumo no front.
        $items = $plan->items()
            ->with(['item:id,name,pricing_type', 'sourcePackage:id,name'])
            ->get()
            ->map(function (TenantPlanItem $planItem) {
                $moduleName = $planItem->item?->name ?? $planItem->module_name;
                $moduleBillingType = $planItem->item?->pricing_type ?? $planItem->billing_type;

                return [
                    'id' => $planItem->item_id,
                    'name' => $moduleName,
                    'billing_type' => $moduleBillingType,
                    'package_id' => $planItem->package_id,
                    'package_name' => $planItem->sourcePackage?->name,
                ];
            })
            ->values();

        // Calcula subtotal e desconto aplicado
        $subtotalAmount = (float) $plan->modules()->sum('value');
        $discountAmount = (float) ($order->coupon_discount_amount ?? 0);

        // Responde com o rascunho e os itens formatados
        return response()->json([
            'order_id'          => $order->id,
            'status'            => $order->status,
            'current_step'      => $order->current_step,
            'amount'            => $subtotalAmount,
            'discount_amount'   => $discountAmount,
            'total_amount'      => $order->total_amount,
            'currency'          => $order->currency,
            'items'             => $items,
        ], 200);
    }

    /**
     * Retorna as opções de uso (tiers) para módulos do pedido.
     */
    public function usageOptions(Request $request)
    {

        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $tenant = $data['tenant'];

        // Busca o rascunho do cliente com itens e configurações
        $order = Order::where('tenant_id', $tenant->id)
            ->where('status', 'draft')
            ->first();

        // Inicia lista de módulos que exigem seleção de uso
        $usageModules = [];

        // Percorre itens do pedido e filtra apenas módulos com cobrança por uso
        foreach ($order->plan->items as $module) {

            if ($module->item->pricing_type != 'Preço Por Uso') {
                continue;
            }

            // Carrega as faixas (tiers) do módulo ordenadas por limite
            $tiers = ModulePricingTier::where('module_id', $module->item->id)
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
            $configItem = $module->configurations->where('key', 'usage')->first();

            if ($configItem) {
                $usageConfig = $configItem->value;
            } elseif (is_array($module->item->pricing_model_snapshot ?? null) && isset($module->item->pricing_model_snapshot['usage'])) {
                $usageConfig = $module->item->pricing_model_snapshot['usage'];
            }

            $usageModules[] = [
                'module_id' => $module->item->id,
                'module_name' => $module->item->name,
                'tiers' => $tiers,
                'selected_usage' => $usageConfig,
            ];
        }

        $plan = $order->plan;
        $currentUsersLimit = (int) ($plan->users_limit ?? 0);
        $currentStorageBytes = (int) ($plan->size_storage ?? 0);
        $currentStorageGb = (int) floor($currentStorageBytes / (1024 * 1024 * 1024));

        $additionalUsers = AdditionalUser::query()
            ->where('status', true)
            ->orderBy('quantity')
            ->get(['quantity', 'price'])
            ->map(function (AdditionalUser $item) {
                return [
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                ];
            })
            ->values()
            ->all();

        $additionalStorages = AdditionalStorage::query()
            ->where('status', true)
            ->orderBy('quantity')
            ->get(['quantity', 'price'])
            ->map(function (AdditionalStorage $item) {
                return [
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'order_id' => $order->id,
            'modules' => $usageModules,
            'users_limit' => $currentUsersLimit,
            'storage_limit_gb' => $currentStorageGb,
            'additional_users' => $additionalUsers,
            'additional_storages' => $additionalStorages,
        ], 200);
    }

    public function details(Request $request, $id)
    {
        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $tenant = $data['tenant'];

        // Busca o pedido do cliente
        $order = Order::where('tenant_id', $tenant->id)->where('id', $id)->first();

        // Formata o pedido
        $orderJson['id'] = $order->id;
        $orderJson['date_created'] = $order->created_at;
        $orderJson['date_paid'] = $order->paid_at;
        $orderJson['amount'] = $order->total_amount;
        $orderJson['method'] = $order->method;
        $orderJson['status'] = $order->status;
        $orderJson['packageName'] = $order->plan->name;

        // Caso não encontre a conta do cliente
        if (!$tenant) {
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
    public function update(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Extrai cliente
        $tenant = $data['tenant'];

        // Obtem o pacote do cliente
        $plan = $this->orderService->getPlanInProgress($tenant);

        // Busca o pedido em andamento
        $order = $this->orderService->getOrderInProgress($tenant, $plan);

        // Realiza ação desejada
        $action = match ($data['action']) {
            'change_package' => $this->changePackage($order, (int) ($data['value'] ?? 0)),
            'change_module' => $this->toggleModule($order, $data['value']),
            'usage' => $this->updateUsage($order, $data['value'] ?? []),
            'limits' => $this->updateLimits($order, $data['value'] ?? []),
            'step' => $this->updateStep($order, $data['value']),
            default => null,
        };

        return $action;

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
     * Aplica um pacote base no rascunho atual.
     * Substitui os módulos do rascunho pelos módulos do pacote selecionado.
     */
    private function changePackage($order, int $selectedPackageId): array
    {
        if ($selectedPackageId <= 0) {
            return [
                'message' => 'Pacote inválido.',
                'action' => 'invalid_package',
            ];
        }

        $selectedPackage = Package::with('modules')
            ->where('status', true)
            ->find($selectedPackageId);

        if (!$selectedPackage) {
            return [
                'message' => 'Pacote não encontrado.',
                'action' => 'package_not_found',
            ];
        }

        $draftPlan = $order->plan;

        if (!$draftPlan) {
            return [
                'message' => 'Pacote em progresso não encontrado.',
                'action' => 'draft_not_found',
            ];
        }

        $existingItemIds = $draftPlan->items()->pluck('id');
        if ($existingItemIds->isNotEmpty()) {
            TenantPlanItemConfiguration::whereIn('item_id', $existingItemIds)->delete();
        }

        $draftPlan->items()->delete();

        foreach ($selectedPackage->modules as $module) {
            $tierId = (int) ($module->pivot->module_pricing_tier_id ?? 0);
            $moduleValue = (float) $module->value;

            $createdItem = TenantPlanItem::create([
                'plan_id'      => $draftPlan->id,
                'package_id'   => $selectedPackage->id,
                'item_id'      => $module->id,
                'module_name'  => $module->name,
                'module_value' => $moduleValue,
                'billing_type' => $module->pricing_type,
                'payload'      => json_encode($module),
            ]);

            if (($module->pricing_type ?? 'Preço Fixo') !== 'Preço Por Uso' || $tierId <= 0) {
                continue;
            }

            $tier = ModulePricingTier::where('id', $tierId)
                ->where('module_id', $module->id)
                ->first();

            if (!$tier) {
                continue;
            }

            TenantPlanItemConfiguration::updateOrCreate(
                [
                    'item_id' => $createdItem->id,
                    'key' => 'usage',
                ],
                [
                    'value' => (string) $tier->usage_limit,
                    'derived_pricing_effect' => [
                        'usage_limit' => (int) $tier->usage_limit,
                        'price' => (float) $tier->price,
                    ],
                    'description' => 'Configuração padrão do pacote aplicada automaticamente.',
                ]
            );
        }

        $this->orderService->recalculateOrderTotals($order);

        return [
            'message' => 'Pacote aplicado com sucesso.',
            'action' => 'changed',
        ];
    }

    /**
     * Adiciona ou remove o módulo
     */
    private function toggleModule($order, $moduleId)
    {
        // Busca dados do módulo
        $module = Module::find($moduleId);

        // Verifica se existe um pacote com esse item
        $plan = $order->plan;

        $existingItem = $plan?->items()->where('item_id', $moduleId)->first();

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
        TenantPlanItem::create([
            'plan_id'      => $plan->id,
            'package_id'   => null,
            'item_id'      => $module->id,
            'module_name'  => $module->name,
            'module_value' => $module->value,
            'billing_type' => $module->pricing_type,
            'payload'      => json_encode($module),
        ]);

        // Recalcula os totais
        $this->orderService->recalculateOrderTotals($order);

        return [
            'message' => 'Módulo adicionado com sucesso.',
            'action' => 'added',
        ];
    }

    /**
     * Salva a configuração de uso do módulo no pedido em andamento.
     */
    private function updateUsage($order, $value): array
    {
        // Extrai identificadores do payload enviado pelo front.
        $moduleId = is_array($value) ? ($value['module_id'] ?? null) : null;
        $usageLimit = is_array($value) ? ($value['usage'] ?? null) : null;

        // Valida dados mínimos obrigatórios para processar o uso.
        if (!$moduleId || !$usageLimit) {
            return [
                'message' => 'Parâmetros de uso inválidos.',
                'action' => 'invalid',
            ];
        }

        $orderItem = $order->plan->items()->where('item_id', $moduleId)->first();

        // Interrompe quando o módulo não existe no pedido atual.
        if (!$orderItem) {
            return [
                'message' => 'Módulo não encontrado no pedido.',
                'action' => 'not_found',
            ];
        }

        // Busca a faixa de preço do módulo com base no limite escolhido.
        $pricingTier = ModulePricingTier::where('module_id', $moduleId)
            ->where('usage_limit', $usageLimit)
            ->first();

        // Garante que o limite selecionado pertence a uma faixa válida.
        if (!$pricingTier) {
            return [
                'message' => 'Faixa de uso inválida para este módulo.',
                'action' => 'invalid_tier',
            ];
        }

        // Persiste a configuração de uso para auditoria e retomada do fluxo.
        TenantPlanItemConfiguration::updateOrCreate(
            [
                'item_id' => $orderItem->id,
                'key' => 'usage',
            ],
            [
                'value' => (string) $usageLimit,
                'value_type' => 'integer',
                'derived_pricing_effect' => [
                    'usage_limit' => (int) $pricingTier->usage_limit,
                    'price' => (float) $pricingTier->price,
                ],
            ]
        );

        $orderItem->save();

        // Recalcula os totais do pedido após alteração de uso.
        $this->orderService->recalculateOrderTotals($order);

        // Retorna status de sucesso para o front.
        return [
            'message' => 'Uso do módulo atualizado com sucesso.',
            'action' => 'updated',
        ];
    }

    /**
     * Atualiza limites adicionais de usuários e armazenamento do plano em rascunho.
     */
    private function updateLimits($order, $value): array
    {
        if (!is_array($value)) {
            return [
                'message' => 'Parâmetros de limite inválidos.',
                'action' => 'invalid',
            ];
        }

        $plan = $order->plan;
        if (!$plan) {
            return [
                'message' => 'Plano em progresso não encontrado.',
                'action' => 'not_found',
            ];
        }

        $usersLimit = isset($value['users_limit']) ? (int) $value['users_limit'] : null;
        $storageLimitGb = isset($value['storage_limit_gb']) ? (int) $value['storage_limit_gb'] : null;

        if ($usersLimit !== null && $usersLimit > 0) {
            $plan->users_limit = $usersLimit;
        }

        if ($storageLimitGb !== null && $storageLimitGb > 0) {
            $plan->size_storage = $storageLimitGb * 1024 * 1024 * 1024;
        }

        $plan->save();

        return [
            'message' => 'Limites atualizados com sucesso.',
            'action' => 'updated',
        ];
    }

    public function cancel(Request $request)
    {

        // Obtém os dados enviados no formulário
        $data = $request->all();

        // Verifica se veio o id do pedido
        if (isset($data['tenant'])) {

            // Encontra o pedido do cliente
            $order = $data['tenant']->lastOrder();

            // Se ele existir atualiza para cancelado
            if ($order) {

                // Inicia o serviço da pagarme
                $pagarme = new PagarMeService();

                // Cancela a assinatura
                $response = $pagarme->cancelSubscription($order->subscription->pagarme_subscription_id);

                // Se a assinatura foi cancelada
                if ((isset($response['status']) && $response['status'] == 'canceled') || $response['message'] == 'This subscription is canceled.') {

                    $order->update([
                        'status' => 'canceled'
                    ]);

                    $order->subscription->update([
                        'status' => 'canceled'
                    ]);

                    // Retorna o cliente atualizado
                    return response()->json([
                        'message' => 'Assinatura cancelada com sucesso'
                    ], 200);
                }
            }
        }

        // Retorna o cliente atualizado
        return response()->json([
            'message' => 'Ocorreu um erro ao cancelar a assinatura. Tente novamente mais tarde.'
        ], 500);
    }
}
