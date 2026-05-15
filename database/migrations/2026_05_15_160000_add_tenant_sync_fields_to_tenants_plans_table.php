<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants_plans', function (Blueprint $table) {
            $table->string('tenant_sync_status')->nullable()->after('status');
            $table->uuid('tenant_sync_request_id')->nullable()->after('tenant_sync_status');
            $table->timestamp('tenant_synced_at')->nullable()->after('tenant_sync_request_id');
            $table->json('tenant_sync_response')->nullable()->after('tenant_synced_at');
            $table->text('tenant_sync_error')->nullable()->after('tenant_sync_response');
        });
    }

    public function down(): void
    {
        Schema::table('tenants_plans', function (Blueprint $table) {
            $table->dropColumn([
                'tenant_sync_status',
                'tenant_sync_request_id',
                'tenant_synced_at',
                'tenant_sync_response',
                'tenant_sync_error',
            ]);
        });
    }
};
