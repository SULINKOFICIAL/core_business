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
        Schema::rename('orders_item_configurations', 'clients_packages_items_configurations');

        Schema::table('clients_packages_items_configurations', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->renameColumn('order_item_id', 'item_id');
            $table->foreign('item_id')->references('id')->on('clients_packages_items')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
