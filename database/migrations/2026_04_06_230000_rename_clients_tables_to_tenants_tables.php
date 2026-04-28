<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename core client tables to tenant naming.
     * This migration is guarded to avoid runtime errors in partial environments.
     */
    public function up(): void
    {
        $this->renameIfExists('clients', 'tenants');
        $this->renameIfExists('clients_cards', 'tenants_cards');
        $this->renameIfExists('clients_domains', 'tenants_domains');
        $this->renameIfExists('clients_integrations', 'tenants_integrations');
        $this->renameIfExists('clients_main_goals', 'tenants_main_goals');
        $this->renameIfExists('clients_metas', 'tenants_metas');
        $this->renameIfExists('clients_modules', 'tenants_modules');
        $this->renameIfExists('clients_packages', 'tenants_plans');
        $this->renameIfExists('clients_packages_items', 'tenants_plans_items');
        $this->renameIfExists('clients_packages_items_configurations', 'tenants_plans_items_configurations');
        $this->renameIfExists('client_provisionings', 'tenant_provisionings');
        $this->renameIfExists('client_runtime_statuses', 'tenant_runtime_statuses');
        $this->renameIfExists('clients_subscriptions', 'tenants_subscriptions');
        $this->renameIfExists('client_subscription_items', 'tenant_subscription_items');
    }

    /**
     * Rollback tenant table names back to client names.
     */
    public function down(): void
    {
        $this->renameIfExists('tenant_subscription_items', 'client_subscription_items');
        $this->renameIfExists('tenants_subscriptions', 'clients_subscriptions');
        $this->renameIfExists('tenant_runtime_statuses', 'client_runtime_statuses');
        $this->renameIfExists('tenant_provisionings', 'client_provisionings');
        $this->renameIfExists('tenants_plans_items_configurations', 'clients_packages_items_configurations');
        $this->renameIfExists('tenants_plans_items', 'clients_packages_items');
        $this->renameIfExists('tenants_plans', 'clients_packages');
        $this->renameIfExists('tenants_modules', 'clients_modules');
        $this->renameIfExists('tenants_metas', 'clients_metas');
        $this->renameIfExists('tenants_main_goals', 'clients_main_goals');
        $this->renameIfExists('tenants_integrations', 'clients_integrations');
        $this->renameIfExists('tenants_domains', 'clients_domains');
        $this->renameIfExists('tenants_cards', 'clients_cards');
        $this->renameIfExists('tenants', 'clients');
    }

    private function renameIfExists(string $from, string $to): void
    {
        if (!Schema::hasTable($from) || Schema::hasTable($to)) {
            return;
        }

        Schema::rename($from, $to);
    }
};
