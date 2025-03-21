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
        Schema::table('orders_items', function (Blueprint $table) {
            // Remova a chave estrangeira existente
            $table->dropForeign('clients_purchases_items_purchase_id_foreign');

            // Renomeie a coluna
            $table->renameColumn('purchase_id', 'order_id');

            // Recrie a chave estrangeira para a nova coluna
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_items', function (Blueprint $table) {
            //
        });
    }
};
