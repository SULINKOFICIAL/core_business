<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Remove estruturas legadas que não participam mais do fluxo canônico.
         */
        Schema::dropIfExists('client_subscription_items');
        Schema::dropIfExists('clients_subscriptions');
        Schema::dropIfExists('order_transactions');

        /**
         * Remove coluna legado já substituída por provider_method.
         */
        if (Schema::hasTable('orders_transactions') && Schema::hasColumn('orders_transactions', 'method')) {
            Schema::table('orders_transactions', function (Blueprint $table) {
                $table->dropColumn('method');
            });
        }
    }

    public function down(): void
    {
        /**
         * Não recria tabelas/colunas legadas removidas intencionalmente.
         */
    }
};

