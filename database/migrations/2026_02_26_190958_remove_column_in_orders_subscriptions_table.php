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
        // Schema::table('orders_subscriptions', function (Blueprint $table) {

        //     // Remove a foreign key primeiro
        //     $table->dropForeign(['order_id']);

        //     // Depois remove a coluna
        //     $table->dropColumn('order_id');
        // });

        // Renomeia a tabela para subscriptions
        // Schema::rename('orders_subscriptions', 'subscriptions');

        // Adiciona um foreignID em orders para subscriptions
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->foreignId('subscription_id')->nullable()->after('package_id')->constrained('subscriptions');
        // });

        // Schema::table('orders_transactions', function (Blueprint $table) {
        //     $table->foreignId('order_id')->nullable()->after('id')->constrained('orders');

        //     $table->dropForeign(['subscription_id']);

        //     $table->unsignedBigInteger('subscription_id')->nullable()->change();

        //     $table->foreign('subscription_id')->references('id')->on('subscriptions');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_subscriptions', function (Blueprint $table) {
            //
        });
    }
};
