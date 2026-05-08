<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\Tenant;
use App\Models\TenantPlan;
use App\Models\TenantPlanItem;
use App\Services\TenantConfigurationSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantManualPlanController extends Controller
{
    public function __construct(private TenantConfigurationSyncService $syncService)
    {
    }

    /**
     * Carrega dados para edição manual administrativa do plano.
     * Fonte de verdade: core_business (plano e ciclo locais).
     */
    public function editData($id)
    {
        // Localiza o tenant alvo da edição administrativa.
        $tenant = Tenant::findOrFail($id);

        // Carrega contexto necessário para montar o pré-preenchimento do modal.
        $tenant->loadMissing(['plan.items', 'subscriptions.cycles']);

        // IDs dos módulos atualmente atribuídos no plano ativo local.
        $enabledModuleIds = collect($tenant->plan?->items ?? [])->pluck('item_id')->filter()->unique()->values();

        // Catálogo de módulos ativos que podem ser manipulados pelo admin.
        $localModules = Module::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'value']);

        // Estrutura final usada no frontend, já marcando o que está habilitado.
        $formattedModules = $localModules->map(function (Module $module) use ($enabledModuleIds) {
            return [
                'id'      => $module->id,
                'name'    => $module->name,
                'slug'    => $module->slug,
                'value'   => (float) $module->value,
                'enabled' => $enabledModuleIds->contains($module->id),
            ];
        })->values();

        // Vigência atual obtida do último ciclo da última assinatura registrada.
        $latestSubscription = $tenant->subscriptions()->latest('id')->first();
        $latestCycle = $latestSubscription?->cycles()->latest('id')->first();

        // Limite em bytes armazenado localmente para converter em GB no formulário.
        $storageLimitBytes = (int) ($tenant->plan?->size_storage ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Dados carregados com sucesso.',
            'data' => [
                'modules'           => $formattedModules,
                'start_date'        => $latestCycle?->start_date ? Carbon::parse($latestCycle->start_date)->format('Y-m-d') : null,
                'end_date'          => $latestCycle?->end_date ? Carbon::parse($latestCycle->end_date)->format('Y-m-d') : null,
                'users_limit'       => (int) ($tenant->plan?->users_limit ?? 0),
                'storage_limit_gb'  => round($storageLimitBytes / (1024 * 1024 * 1024), 2),
            ],
        ]);
    }

    /**
     * Aplica atualização manual administrativa do plano sem cobrança.
     * Regra: ajuste operacional imediato para administradores.
     */
    public function apply(Request $request, $id)
    {
        // Tenant alvo da operação manual.
        $tenant = Tenant::findOrFail($id);

        // Fluxo otimista: recebe payload direto do modal sem validação formal.
        $data = $request->all();

        // Conjunto final de módulos que deverão permanecer habilitados.
        $selectedModuleIds = collect($data['modules'] ?? [])->map(fn ($moduleId) => (int) $moduleId)->unique()->values();
        $selectedModules   = Module::whereIn('id', $selectedModuleIds)->get();

        // Conversões para persistência local.
        $storageLimitBytes = (int) round(((float) ($data['storage_limit_gb'] ?? 0)) * 1024 * 1024 * 1024);
        $planValue         = (float) $selectedModules->sum('value');

        DB::transaction(function () use ($tenant, $selectedModules, $selectedModuleIds, $data, $storageLimitBytes, $planValue) {
            // Garante plano ativo local; cria quando inexistente.
            $activePlan = $tenant->plan ?: TenantPlan::create([
                'tenant_id'    => $tenant->id,
                'name'         => 'Plano Manual Admin',
                'value'        => 0,
                'users_limit'  => 0,
                'size_storage' => 0,
                'progress'     => 'completed',
                'status'       => true,
                'created_by'   => Auth::id(),
            ]);

            // Atualiza metadados e limites do plano no core_business (fonte de verdade).
            $activePlan->update([
                'name'         => $activePlan->name ?: 'Plano Manual Admin',
                'value'        => $planValue,
                'users_limit'  => (int) ($data['users_limit'] ?? 0),
                'size_storage' => $storageLimitBytes,
                'progress'     => 'completed',
                'status'       => true,
                'updated_by'   => Auth::id(),
            ]);

            // Reconstrói os itens do plano com base na seleção atual do admin.
            $activePlan->items()->delete();

            foreach ($selectedModules as $module) {
                $basePrice = (float) $module->value;
                TenantPlanItem::create([
                    'plan_id'      => $activePlan->id,
                    'package_id'   => null,
                    'item_id'      => $module->id,
                    'item_type'    => 'module',
                    'module_name'  => $module->name,
                    'base_price'   => $basePrice,
                    'applied_price' => $basePrice,
                    'discount_amount' => 0,
                    'discount_percent' => 0,
                    'pricing_source' => 'manual_admin',
                    'billing_type' => $module->pricing_type,
                    'payload'      => $module->toJson(),
                ]);
            }

            // Vincula/atualiza assinatura local ao plano ativo.
            $subscription = Subscription::where('tenant_id', $tenant->id)
                ->where('plan_id', $activePlan->id)
                ->latest('id')
                ->first();

            if (!$subscription) {
                $subscription = Subscription::create([
                    'tenant_id'                 => $tenant->id,
                    'plan_id'                   => $activePlan->id,
                    'provider'                  => 'pagarme',
                    'provider_subscription_id'  => 'manual-admin-' . $tenant->id,
                    'payment_method'            => 'manual_admin',
                    'currency'                  => 'BRL',
                    'installments'              => 1,
                    'status'                    => 'active',
                ]);
            } else {
                $subscription->update([
                    'provider' => 'pagarme',
                    'plan_id' => $activePlan->id,
                    'status' => 'active',
                ]);
            }

            // Mantém a vigência local alinhada ao período informado no modal.
            // Regra: reutiliza o mesmo provider_cycle_id quando já existir ciclo billed.
            $existingCycle = SubscriptionCycle::where('subscription_id', $subscription->id)
                ->where('status', 'billed')
                ->latest('id')
                ->first();

            $cyclePayload = [
                'subscription_id' => $subscription->id,
                'provider' => 'pagarme',
                'provider_cycle_id' => $existingCycle?->provider_cycle_id ?: 'manual-cycle-' . $subscription->id,
                'start_date' => Carbon::parse($data['start_date'] ?? now())->startOfDay(),
                'end_date' => Carbon::parse($data['end_date'] ?? now())->endOfDay(),
                'status' => 'billed',
                'cycle' => (string) ($existingCycle?->cycle ?: 1),
                'billing_at' => Carbon::parse($data['start_date'] ?? now())->startOfDay(),
                'next_billing_at' => Carbon::parse($data['end_date'] ?? now())->endOfDay(),
            ];

            if ($existingCycle) {
                $existingCycle->update($cyclePayload);
            } else {
                SubscriptionCycle::create($cyclePayload);
            }
        });

        /**
         * Dispara a sincronização consolidada para o tenant remoto
         * após persistir o estado final do plano local.
         */
        $syncResults = $this->syncService->syncFromCurrentPlan(
            $tenant,
            source: 'manual_admin',
            operatorId: Auth::id(),
            reason: $data['manual_change_reason'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
        );

        return response()->json([
            'success' => (bool) ($syncResults['success'] ?? false),
            'message' => 'Plano do cliente atualizado com sucesso.',
            'sync' => $syncResults,
        ]);
    }
}
