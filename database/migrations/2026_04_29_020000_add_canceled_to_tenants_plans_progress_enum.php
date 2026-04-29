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

        DB::statement("
            ALTER TABLE `tenants_plans`
            MODIFY `progress` ENUM('draft', 'in_progress', 'completed', 'canceled')
            NOT NULL DEFAULT 'draft'
        ");
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
            ->where('progress', 'canceled')
            ->update(['progress' => 'draft']);

        DB::statement("
            ALTER TABLE `tenants_plans`
            MODIFY `progress` ENUM('draft', 'in_progress', 'completed')
            NOT NULL DEFAULT 'draft'
        ");
    }
};
