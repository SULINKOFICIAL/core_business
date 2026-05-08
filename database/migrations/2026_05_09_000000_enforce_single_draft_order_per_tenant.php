<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Mantém apenas um draft por tenant antes de aplicar a restrição.
         * Critério: preserva o draft de maior id.
         */
        $duplicateTenantIds = DB::table('orders')
            ->select('tenant_id')
            ->where('status', 'draft')
            ->groupBy('tenant_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('tenant_id');

        foreach ($duplicateTenantIds as $tenantId) {
            $keepId = DB::table('orders')
                ->where('tenant_id', $tenantId)
                ->where('status', 'draft')
                ->max('id');

            DB::table('orders')
                ->where('tenant_id', $tenantId)
                ->where('status', 'draft')
                ->where('id', '!=', $keepId)
                ->delete();
        }

        if (!Schema::hasColumn('orders', 'draft_tenant_lock')) {
            DB::statement("
                ALTER TABLE orders
                ADD COLUMN draft_tenant_lock BIGINT
                GENERATED ALWAYS AS (
                    CASE
                        WHEN status = 'draft' THEN tenant_id
                        ELSE NULL
                    END
                ) STORED
            ");
        }

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'orders')
            ->where('index_name', 'orders_draft_tenant_lock_unique')
            ->exists();

        if (!$indexExists) {
            DB::statement('CREATE UNIQUE INDEX orders_draft_tenant_lock_unique ON orders (draft_tenant_lock)');
        }
    }

    public function down(): void
    {
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'orders')
            ->where('index_name', 'orders_draft_tenant_lock_unique')
            ->exists();

        if ($indexExists) {
            DB::statement('DROP INDEX orders_draft_tenant_lock_unique ON orders');
        }

        if (Schema::hasColumn('orders', 'draft_tenant_lock')) {
            DB::statement('ALTER TABLE orders DROP COLUMN draft_tenant_lock');
        }
    }
};

