<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("\n            ALTER TABLE `tenants_plans`\n            MODIFY `progress` ENUM('draft', 'pending', 'completed', 'canceled')\n            NOT NULL DEFAULT 'draft'\n        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("\n            ALTER TABLE `tenants_plans`\n            MODIFY `progress` ENUM('draft', 'completed', 'canceled')\n            NOT NULL DEFAULT 'draft'\n        ");
    }
};
