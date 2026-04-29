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
        DB::table('tenants_plans')
            ->where('progress', 'in_progress')
            ->update([
                'progress' => 'draft',
                'updated_at' => now(),
            ]);

        DB::statement("
            ALTER TABLE `tenants_plans`
            MODIFY `progress` ENUM('draft', 'completed', 'canceled')
            NOT NULL DEFAULT 'draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE `tenants_plans`
            MODIFY `progress` ENUM('draft', 'in_progress', 'completed', 'canceled')
            NOT NULL DEFAULT 'draft'
        ");
    }
};

