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
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            $table->foreignId('subscription_id')->after('id')->constrained('orders_subscriptions');
            $table->string('pagarme_transaction_id')->after('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_transactions', function (Blueprint $table) {
            //
        });
    }
};
