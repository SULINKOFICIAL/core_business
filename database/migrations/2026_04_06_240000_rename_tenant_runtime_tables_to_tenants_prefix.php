<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameIfExists('tenant_provisionings', 'tenants_provisionings');
        $this->renameIfExists('tenant_runtime_statuses', 'tenants_runtime_statuses');
        $this->renameIfExists('tenant_subscription_items', 'tenants_subscription_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameIfExists('tenants_subscription_items', 'tenant_subscription_items');
        $this->renameIfExists('tenants_runtime_statuses', 'tenant_runtime_statuses');
        $this->renameIfExists('tenants_provisionings', 'tenant_provisionings');
    }

    private function renameIfExists(string $from, string $to): void
    {
        if (!Schema::hasTable($from) || Schema::hasTable($to)) {
            return;
        }

        Schema::rename($from, $to);
    }
};

