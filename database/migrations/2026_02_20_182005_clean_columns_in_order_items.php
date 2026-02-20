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
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'action', 'item_code', 'item_name_snapshot', 'item_reference_type', 'item_reference_id', 'quantity', 'unit_price_snapshot', 'subtotal_amount', 'pricing_model_snapshot', 'rules_snapshot', 'start_date', 'end_date']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->text('payload')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
