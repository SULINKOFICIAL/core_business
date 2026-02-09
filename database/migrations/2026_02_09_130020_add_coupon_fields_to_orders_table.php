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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('coupon_discount_amount', 12, 2)->default(0)->after('total_amount');
            $table->foreignId('coupon_id')->nullable()->after('rules_snapshot')->constrained('coupons');
            $table->string('coupon_code_snapshot')->nullable()->after('coupon_id');
            $table->string('coupon_type_snapshot')->nullable()->after('coupon_code_snapshot');
            $table->decimal('coupon_value_snapshot', 12, 2)->nullable()->after('coupon_type_snapshot');
            $table->unsignedInteger('coupon_trial_months')->nullable()->after('coupon_value_snapshot');
            $table->dateTime('coupon_applied_at')->nullable()->after('coupon_trial_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'coupon_discount_amount',
                'coupon_id',
                'coupon_code_snapshot',
                'coupon_type_snapshot',
                'coupon_value_snapshot',
                'coupon_trial_months',
                'coupon_applied_at',
            ]);
        });
    }
};
