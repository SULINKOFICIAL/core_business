<?php

namespace App\Http\Controllers;

use App\Models\TenantPlan;
use App\Models\TenantPlanItem;
use App\Models\Module;
use App\Services\OrderService;
use Illuminate\Http\Request;

class TenantsPlansController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    /**
     * Retorna o plano em progresso do cliente.
     */
    public function plan(Request $request)
    {
        $tenant = $request->all()['tenant'];
        $plan = $this->orderService->getPlanInProgress($tenant);

        return response()->json([
            'plan' => $plan->load('modules'),
        ]);
    }

    /**
     * Atualiza o plano em rascunho com base nos módulos desejados.
     */
    public function update(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Extrai cliente
        $tenant = $data['tenant'];

        // Cria ou atualiza o plano em rascunho com os módulos enviados
        $plan = $this->orderService->getPlanInProgress($tenant);

        // Realiza ação desejada
        $action = match ($data['action']) {
            'change_module' => $this->toggleModule($plan, $data['value']),
            default => null,
        };

        // Retorna resposta
        return response()->json([
            'message' => $action['message'],
            'plan' => $plan,
        ]);
    }

    /**
     * Adiciona ou remove o módulo
     */
    private function toggleModule(TenantPlan $plan, $moduleId)
    {
        // Busca dados do módulo
        $module = Module::find($moduleId);

        // Verifica se esse pedido já tem esse item
        $existingItem = TenantPlanItem::where('plan_id', $plan->id)
            ->where('item_id', $moduleId)
            ->first();

        // Se o módulo já existe, remove
        if ($existingItem) {
            $existingItem->delete();

            // Recalcula os totais
            $this->recalculatePlanTotal($plan);

            return [
                'message' => 'Módulo removido com sucesso.',
                'action' => 'removed',
            ];
        }

        // Cria item de módulo no pedido
        TenantPlanItem::create([
            'plan_id' => $plan->id,
            'item_id' => $module->id,
            'module_name' => $module->name,
            'module_value' => $module->value,
            'billing_type' => $module->pricing_type,
            'payload' => json_encode($module),
        ]);

        // Recalcula os totais
        $this->recalculatePlanTotal($plan);

        return [
            'message' => 'Módulo adicionado com sucesso.',
            'action' => 'added',
        ];
    }

    /**
     * Ajusta o preço do plano
     */
    public function recalculatePlanTotal(TenantPlan $plan)
    {
        // Soma o subtotal atual caso não seja informado
        $itemsSubtotal = $plan->modules()->sum('value');

        // Calcula o total final do pedido
        $totalAmount = $itemsSubtotal;
        if ($totalAmount < 0) {
            $totalAmount = 0.0;
        }

        $plan->update([
            'value' => $totalAmount,
        ]);
    }
}
