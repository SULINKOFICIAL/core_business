<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'onboarding_current_step')) {
                $table->enum('onboarding_current_step', ['account', 'company', 'goal', 'address'])
                    ->nullable()
                    ->after('document_type');
            }

            if (!Schema::hasColumn('tenants', 'onboarding_started_at')) {
                $table->timestamp('onboarding_started_at')->nullable()->after('onboarding_current_step');
            }

            if (!Schema::hasColumn('tenants', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            $columns = [
                'onboarding_current_step',
                'onboarding_started_at',
                'onboarding_completed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
