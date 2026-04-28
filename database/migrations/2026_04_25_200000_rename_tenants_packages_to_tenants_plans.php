<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename legacy tenants_packages tables to tenants_plans naming.
     */
    public function up(): void
    {
        $this->renameIfExists('tenants_packages', 'tenants_plans');
        $this->renameIfExists('tenants_packages_items', 'tenants_plans_items');
        $this->renameIfExists('tenants_packages_items_configurations', 'tenants_plans_items_configurations');
    }

    /**
     * Rollback plan tables to legacy package naming.
     */
    public function down(): void
    {
        $this->renameIfExists('tenants_plans_items_configurations', 'tenants_packages_items_configurations');
        $this->renameIfExists('tenants_plans_items', 'tenants_packages_items');
        $this->renameIfExists('tenants_plans', 'tenants_packages');
    }

    private function renameIfExists(string $from, string $to): void
    {
        if (!Schema::hasTable($from) || Schema::hasTable($to)) {
            return;
        }

        Schema::rename($from, $to);
    }
};
