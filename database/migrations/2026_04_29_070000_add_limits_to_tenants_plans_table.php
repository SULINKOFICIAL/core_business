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
        Schema::table('tenants_plans', function (Blueprint $table) {
            $table->unsignedInteger('users_limit')->default(0)->after('value');
            $table->unsignedBigInteger('size_storage')->default(0)->after('users_limit');
        });

        $plans = DB::table('tenants_plans')
            ->select('id', 'tenant_id')
            ->get();

        foreach ($plans as $plan) {
            $usersLimit = (int) (DB::table('tenants')
                ->where('id', $plan->tenant_id)
                ->value('users_limit') ?? 0);

            $packageId = DB::table('tenants_plans_items')
                ->where('plan_id', $plan->id)
                ->whereNotNull('package_id')
                ->orderByDesc('id')
                ->value('package_id');

            $sizeStorage = 0;
            if ($packageId) {
                $sizeStorage = (int) (DB::table('packages')
                    ->where('id', $packageId)
                    ->value('size_storage') ?? 0);
            }

            DB::table('tenants_plans')
                ->where('id', $plan->id)
                ->update([
                    'users_limit' => $usersLimit,
                    'size_storage' => $sizeStorage,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants_plans', function (Blueprint $table) {
            $table->dropColumn(['users_limit', 'size_storage']);
        });
    }
};

