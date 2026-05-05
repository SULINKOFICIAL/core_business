<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $foreignKey = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'tenants_subscriptions')
            ->where('COLUMN_NAME', 'package_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($foreignKey) {
            DB::statement("ALTER TABLE `tenants_subscriptions` DROP FOREIGN KEY `{$foreignKey}`");
        }

        Schema::table('tenants_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('tenants_subscriptions', 'package_id')) {
                $table->dropColumn('package_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants_subscriptions', 'package_id')) {
                $table->foreignId('package_id')->nullable()->after('order_id')->constrained('packages');
            }
        });
    }
};
