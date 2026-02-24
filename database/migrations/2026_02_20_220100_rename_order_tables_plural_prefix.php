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
        if (Schema::hasTable('order_item_configurations') && !Schema::hasTable('orders_item_configurations')) {
            Schema::rename('order_item_configurations', 'orders_item_configurations');
        }

        if (Schema::hasTable('order_items') && !Schema::hasTable('orders_items')) {
            Schema::rename('order_items', 'orders_items');
        }

        if (Schema::hasTable('order_transactions') && !Schema::hasTable('orders_transactions')) {
            Schema::rename('order_transactions', 'orders_transactions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders_item_configurations') && !Schema::hasTable('order_item_configurations')) {
            Schema::rename('orders_item_configurations', 'order_item_configurations');
        }

        if (Schema::hasTable('orders_items') && !Schema::hasTable('order_items')) {
            Schema::rename('orders_items', 'order_items');
        }

        if (Schema::hasTable('orders_transactions') && !Schema::hasTable('order_transactions')) {
            Schema::rename('orders_transactions', 'order_transactions');
        }
    }
};
