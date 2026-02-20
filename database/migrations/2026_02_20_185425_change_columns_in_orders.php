<?php

use App\Models\Order;
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

        Order::whereNotNull('id')->update(['current_step' => 'Módulos']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('current_step', ['Módulos', 'Uso', 'Pagamento'])->default('Módulos')->change();
        });

         Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'coupon_discount_amount',
                'pricing_snapshot',
                'rules_snapshot',
                'coupon_id',
                'coupon_code_snapshot',
                'coupon_type_snapshot',
                'coupon_value_snapshot',
                'coupon_trial_months',
                'coupon_applied_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
