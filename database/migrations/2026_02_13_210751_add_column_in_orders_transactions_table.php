<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('order_transactions', 'order_id')) {
                $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', DB::getDatabaseName())
                    ->where('TABLE_NAME', 'order_transactions')
                    ->where('COLUMN_NAME', 'order_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->exists();

                if ($fkExists) {
                    $table->dropForeign(['order_id']);
                }

                $table->dropColumn('order_id');
            }

            if (! Schema::hasColumn('order_transactions', 'subscription_id')) {
                $table->foreignId('subscription_id')->after('id')->constrained('orders_subscriptions');
            }

            if (! Schema::hasColumn('order_transactions', 'pagarme_transaction_id')) {
                $table->string('pagarme_transaction_id')->after('subscription_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('order_transactions', 'pagarme_transaction_id')) {
                $table->dropColumn('pagarme_transaction_id');
            }

            if (Schema::hasColumn('order_transactions', 'subscription_id')) {
                $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', DB::getDatabaseName())
                    ->where('TABLE_NAME', 'order_transactions')
                    ->where('COLUMN_NAME', 'subscription_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->exists();

                if ($fkExists) {
                    $table->dropForeign(['subscription_id']);
                }

                $table->dropColumn('subscription_id');
            }

            if (! Schema::hasColumn('order_transactions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('id');
            }
        });
    }
};
