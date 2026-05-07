<?php

namespace App\Services\Payments;

use App\Models\TenantPlan;

class ActivePlanService
{
    /**
     *
     * Busca o último plano ativo concluído do tenant para vincular aos pagamentos.
     *
     */
    public function findActivePlan(int $tenantId): ?TenantPlan
    {
        $plan = TenantPlan::where('tenant_id', $tenantId)
                            ->where('progress', 'pending')
                            ->where('status', true)
                            ->orderBy('id', 'desc')
                            ->first();

        if (!$plan) {
            throw new \Exception('Nenhum plano ativo encontrado para o tenant');
        }

        return $plan;
    }
}
