<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tenants_plans') || !Schema::hasColumn('tenants_plans', 'size_storage')) {
            return;
        }

        $thirtyGbInBytes = 30 * 1024 * 1024 * 1024;

        DB::table('tenants_plans')->update([
            'size_storage' => $thirtyGbInBytes,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        /**
         * Sem rollback destrutivo para não sobrescrever limites ajustados manualmente.
         */
    }
};
