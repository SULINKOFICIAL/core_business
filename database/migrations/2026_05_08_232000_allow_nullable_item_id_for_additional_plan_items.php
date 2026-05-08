<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants_plans_items') || !Schema::hasColumn('tenants_plans_items', 'item_id')) {
            return;
        }

        $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'tenants_plans_items')
            ->where('COLUMN_NAME', 'item_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if ($foreignKeyExists) {
            DB::statement('ALTER TABLE `tenants_plans_items` DROP FOREIGN KEY `tenants_plans_items_item_id_foreign`');
        }

        Schema::table('tenants_plans_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->change();
        });

        DB::statement('ALTER TABLE `tenants_plans_items` ADD CONSTRAINT `tenants_plans_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `modules`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenants_plans_items') || !Schema::hasColumn('tenants_plans_items', 'item_id')) {
            return;
        }

        DB::statement('ALTER TABLE `tenants_plans_items` DROP FOREIGN KEY `tenants_plans_items_item_id_foreign`');

        Schema::table('tenants_plans_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable(false)->change();
        });

        DB::statement('ALTER TABLE `tenants_plans_items` ADD CONSTRAINT `tenants_plans_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }
};

