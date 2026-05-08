<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $periodStart = Carbon::create(2026, 1, 1, 0, 0, 0);
        $periodEnd = Carbon::create(2026, 12, 31, 23, 59, 59);

        $tenants = DB::table('tenants')
            ->select('id', 'name', 'status')
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            return;
        }

        $activeModules = DB::table('modules')
            ->select('id', 'name', 'slug', 'value', 'pricing_type')
            ->where('status', true)
            ->orderBy('id')
            ->get();

        if ($activeModules->isEmpty()) {
            throw new RuntimeException('Nenhum módulo ativo encontrado para reconstrução global de planos.');
        }

        $sulinkTenantIds = DB::table('tenants as tenants')
            ->leftJoin('tenants_domains as domains', 'domains.tenant_id', '=', 'tenants.id')
            ->where(function ($query) {
                $query->whereRaw('LOWER(tenants.name) like ?', ['%sulink%'])
                    ->orWhereRaw('LOWER(domains.domain) like ?', ['%sulink%']);
            })
            ->pluck('tenants.id')
            ->unique()
            ->values();

        if ($sulinkTenantIds->count() !== 1) {
            throw new RuntimeException('Identificação do tenant Sulink falhou. Esperado exatamente 1 tenant, encontrado: ' . $sulinkTenantIds->count() . '. IDs: [' . $sulinkTenantIds->implode(', ') . ']');
        }

        $sulinkTenantId = $sulinkTenantIds->first();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            /**
             * Ordem de limpeza por dependência.
             */
            DB::table('orders_transactions')->truncate();
            DB::table('orders')->truncate();
            DB::table('subscriptions_cycles')->truncate();
            DB::table('subscriptions')->truncate();
            DB::table('tenants_plans_items_configurations')->truncate();
            DB::table('tenants_plans_items')->truncate();
            DB::table('tenants_plans')->truncate();

            foreach ($tenants as $tenant) {
                $usersLimit = intval($tenant->id) === intval($sulinkTenantId) ? 30 : 10;
                $planValue = floatval($activeModules->sum('value'));

                $planId = DB::table('tenants_plans')->insertGetId([
                    'tenant_id' => $tenant->id,
                    'name' => 'Plano Liberado 2026',
                    'value' => $planValue,
                    'users_limit' => $usersLimit,
                    'size_storage' => 0,
                    'progress' => 'completed',
                    'status' => true,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $planItems = [];

                foreach ($activeModules as $module) {
                    $planItems[] = [
                        'plan_id' => $planId,
                        'package_id' => null,
                        'item_id' => $module->id,
                        'module_name' => $module->name,
                        'module_value' => floatval($module->value),
                        'billing_type' => $module->pricing_type,
                        'payload' => json_encode($module),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('tenants_plans_items')->insert($planItems);

                $subscriptionId = DB::table('subscriptions')->insertGetId([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $planId,
                    'provider' => 'manual_admin',
                    'provider_subscription_id' => 'manual-admin-' . $tenant->id,
                    'provider_card_id' => null,
                    'interval' => 'year',
                    'payment_method' => 'manual_admin',
                    'currency' => 'BRL',
                    'installments' => 1,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $orderId = DB::table('orders')->insertGetId([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $planId,
                    'subscription_id' => $subscriptionId,
                    'status' => 'paid',
                    'provider' => 'manual_admin',
                    'provider_method' => 'manual_admin',
                    'current_step' => 'Pagamento',
                    'currency' => 'BRL',
                    'total_amount' => $planValue,
                    'provider_message' => 'Reconstrucao global de planos/ciclos 2026',
                    'paid_at' => $now,
                    'locked_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('subscriptions')
                    ->where('id', $subscriptionId)
                    ->update([
                        'order_id' => $orderId,
                        'updated_at' => $now,
                    ]);

                DB::table('subscriptions_cycles')->insert([
                    'subscription_id' => $subscriptionId,
                    'provider' => 'manual_admin',
                    'provider_cycle_id' => 'manual-cycle-' . $subscriptionId,
                    'start_date' => $periodStart,
                    'end_date' => $periodEnd,
                    'status' => 'billed',
                    'cycle' => '1',
                    'billing_at' => $periodStart,
                    'next_billing_at' => $periodEnd,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('orders_transactions')->insert([
                    'order_id' => $orderId,
                    'subscription_id' => $subscriptionId,
                    'provider' => 'manual_admin',
                    'provider_method' => 'manual_admin',
                    'provider_transaction_id' => 'manual-tx-' . $orderId,
                    'gateway_id' => null,
                    'gateway_code' => null,
                    'external_transaction_id' => null,
                    'status' => 'paid',
                    'amount' => 0,
                    'currency' => 'BRL',
                    'recurrency' => null,
                    'brand_tid_at' => null,
                    'brand_tid' => null,
                    'response' => json_encode(['source' => 'global_rebuild_2026']),
                    'raw_response_snapshot' => json_encode(['source' => 'global_rebuild_2026']),
                    'authorized_at' => $now,
                    'paid_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
