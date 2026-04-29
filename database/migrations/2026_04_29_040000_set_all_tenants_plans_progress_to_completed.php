<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tenants_plans') || !Schema::hasColumn('tenants_plans', 'progress')) {
            return;
        }

        DB::table('tenants_plans')->update([
            'progress' => 'completed',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tenants_plans') || !Schema::hasColumn('tenants_plans', 'progress')) {
            return;
        }

        DB::table('tenants_plans')
            ->where('progress', 'completed')
            ->update([
                'progress' => 'draft',
                'updated_at' => now(),
            ]);
    }
};

