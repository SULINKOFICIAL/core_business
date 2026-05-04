<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('tenants_subscriptions', 'package_id')) {
                $table->dropForeign(['package_id']);
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
