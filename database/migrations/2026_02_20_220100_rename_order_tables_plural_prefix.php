<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('order_transactions') && !Schema::hasTable('orders_transactions')) {
            Schema::rename('order_transactions', 'orders_transactions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders_transactions') && !Schema::hasTable('order_transactions')) {
            Schema::rename('orders_transactions', 'order_transactions');
        }
    }
};
