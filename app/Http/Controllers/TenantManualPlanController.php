<?php

namespace App\Http\Controllers;

use App\Models\LogsApi;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\Tenant;
use App\Models\TenantPlan;
use App\Models\TenantPlanItem;
use App\Services\GuzzleService;
use App\Services\ModuleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantManualPlanController extends Controller
{
    /**
     * Carrega dados para edição manual administrativa do plano.
     * Fonte de verdade: core_business (plano e ciclo locais).
     */
    public function editData($id)
    {
        // Localiza o tenant alvo da edição administrativa.
        $tenant = Tenant::query()->findOrFail($id);

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
        $tenant = Tenant::query()->findOrFail($id);

        // Fluxo otimista: recebe payload direto do modal sem validação formal.
        $data = $request->all();

        // Conjunto final de módulos que deverão permanecer habilitados.
        $selectedModuleIds = collect($data['modules'] ?? [])->map(fn ($moduleId) => (int) $moduleId)->unique()->values();
        $selectedModules   = Module::query()->whereIn('id', $selectedModuleIds)->get();

        // Base de módulos ativos para desativação determinística no tenant remoto.
        $allActiveModules  = Module::query()->where('status', true)->get();

        // Conversões para persistência local.
        $storageLimitBytes = (int) round(((float) ($data['storage_limit_gb'] ?? 0)) * 1024 * 1024 * 1024);
        $planValue         = (float) $selectedModules->sum('value');

        // Snapshot do estado anterior para trilha de auditoria.
        $before            = $this->snapshotManualPlanState($tenant);

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
                TenantPlanItem::create([
                    'plan_id'      => $activePlan->id,
                    'package_id'   => null,
                    'item_id'      => $module->id,
                    'module_name'  => $module->name,
                    'module_value' => (float) $module->value,
                    'billing_type' => $module->pricing_type,
                    'payload'      => $module->toJson(),
                ]);
            }

            // Vincula/atualiza assinatura local ao plano ativo.
            $subscription = Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('plan_id', $activePlan->id)
                ->latest('id')
                ->first();

            if (!$subscription) {
                $subscription = Subscription::create([
                    'tenant_id'               => $tenant->id,
                    'plan_id'                 => $activePlan->id,
                    'pagarme_subscription_id' => 'manual-admin-' . $tenant->id,
                    'payment_method'          => 'manual_admin',
                    'currency'                => 'BRL',
                    'installments'            => 1,
                    'status'                  => 'active',
                ]);
            } else {
                $subscription->update([
                    'plan_id' => $activePlan->id,
                    'status' => 'active',
                ]);
            }

            // Mantém a vigência local alinhada ao período informado no modal.
            SubscriptionCycle::updateOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'status' => 'billed',
                ],
                [
                    'pagarme_cycle_id' => null,
                    'start_date' => Carbon::parse($data['start_date'] ?? now())->startOfDay(),
                    'end_date' => Carbon::parse($data['end_date'] ?? now())->endOfDay(),
                    'cycle' => 1,
                    'billing_at' => Carbon::parse($data['start_date'] ?? now())->startOfDay(),
                    'next_billing_at' => Carbon::parse($data['end_date'] ?? now())->endOfDay(),
                ]
            );
        });

        // Serviço responsável pela propagação das alterações ao coresulink.
        $moduleService = app(ModuleService::class);

        /**
         * Sincronização determinística:
         * 1) desabilita todos os módulos ativos conhecidos na central
         * 2) habilita apenas os selecionados no ajuste manual
         */
        $syncModulesDisabled = $moduleService->configureModules($tenant, $allActiveModules->pluck('id')->all(), false);
        $syncModulesEnabled  = $moduleService->configureModules($tenant, $selectedModuleIds->all(), true);

        // Sincroniza período e limites no tenant remoto.
        $syncSubscription    = $moduleService->createSubscriptionCore($tenant, $data['start_date'] ?? now()->format('Y-m-d'), $data['end_date'] ?? now()->format('Y-m-d'));
        $syncUsers           = $moduleService->updateUsersLimitsCore($tenant, (int) ($data['users_limit'] ?? 0));
        $syncStorage         = $moduleService->updateSizeStorageCore($tenant, $storageLimitBytes);

        // Consolida retornos para auditoria operacional.
        $syncResults = [
            'modules_enabled'  => $syncModulesEnabled,
            'modules_disabled' => $syncModulesDisabled,
            'subscription'     => $syncSubscription,
            'users_limit'      => $syncUsers,
            'storage_limit'    => $syncStorage,
        ];

        // Snapshot pós-operação para comparação em log.
        $after = $this->snapshotManualPlanState($tenant->fresh(['plan.items.item', 'subscriptions.cycles']));

        // Registra o evento administrativo completo para rastreabilidade.
        LogsApi::create([
            'api'       => 'PLAN_MANUAL_ADMIN',
            'tenant_id' => $tenant->id,
            'status'    => 'success',
            'json'      => json_encode([
                'operator_id' => Auth::id(),
                'reason'      => $data['manual_change_reason'] ?? '',
                'payload'     => $data,
                'before'      => $before,
                'after'       => $after,
                'sync'        => $syncResults,
                'note'        => 'Fluxo administrativo sem cobrança com fonte de verdade no core_business.',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plano do cliente atualizado com sucesso.',
        ]);
    }

    /**
     * Snapshot simplificado para auditoria da alteração manual.
     */
    private function snapshotManualPlanState(Tenant $tenant): array
    {
        // Garante que os relacionamentos usados no snapshot estão carregados.
        $tenant->loadMissing(['plan.items.item', 'subscriptions.cycles']);

        // Estado corrente resumido do plano e assinatura para auditoria.
        $activePlan = $tenant->plan;
        $latestSubscription = $tenant->subscriptions()->latest('id')->first();
        $latestCycle = $latestSubscription?->cycles()->latest('id')->first();

        return [
            'plan' => [
                'id'            => $activePlan?->id,
                'name'          => $activePlan?->name,
                'value'         => $activePlan?->value,
                'users_limit'   => $activePlan?->users_limit,
                'size_storage'  => $activePlan?->size_storage,
                'modules'       => $activePlan?->items?->map(function (TenantPlanItem $item) {
                    return [
                        'module_id' => $item->item_id,
                        'name'      => $item->module_name,
                    ];
                })->values()->all() ?? [],
            ],
            'subscription' => [
                'id'            => $latestSubscription?->id,
                'status'        => $latestSubscription?->status,
                'cycle_start'   => $latestCycle?->start_date,
                'cycle_end'     => $latestCycle?->end_date,
            ],
        ];
    }
}
