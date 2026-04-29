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
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants');
            }

            if (!Schema::hasColumn('subscriptions', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('tenant_id')->constrained('tenants_plans');
            }

            if (!Schema::hasColumn('subscriptions', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('plan_id')->constrained('orders');
            }
        });

        $orders = DB::table('orders')
            ->select('subscription_id', 'id', 'tenant_id', 'plan_id')
            ->whereNotNull('subscription_id')
            ->orderByDesc('id')
            ->get()
            ->unique('subscription_id');

        foreach ($orders as $order) {
            DB::table('subscriptions')
                ->where('id', $order->subscription_id)
                ->update([
                    'order_id' => $order->id,
                    'tenant_id' => $order->tenant_id,
                    'plan_id' => $order->plan_id,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }

            if (Schema::hasColumn('subscriptions', 'plan_id')) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            }

            if (Schema::hasColumn('subscriptions', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};

