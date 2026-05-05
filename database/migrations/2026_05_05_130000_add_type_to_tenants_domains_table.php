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
        if (Schema::hasTable('tenants_domains') && !Schema::hasColumn('tenants_domains', 'type')) {
            Schema::table('tenants_domains', function (Blueprint $table) {
                $table->string('type')->default('Principal')->after('domain');
            });

            return;
        }

        if (Schema::hasTable('clients_domains') && !Schema::hasColumn('clients_domains', 'type')) {
            Schema::table('clients_domains', function (Blueprint $table) {
                $table->string('type')->default('Principal')->after('domain');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tenants_domains') && Schema::hasColumn('tenants_domains', 'type')) {
            Schema::table('tenants_domains', function (Blueprint $table) {
                $table->dropColumn('type');
            });

            return;
        }

        if (Schema::hasTable('clients_domains') && Schema::hasColumn('clients_domains', 'type')) {
            Schema::table('clients_domains', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
