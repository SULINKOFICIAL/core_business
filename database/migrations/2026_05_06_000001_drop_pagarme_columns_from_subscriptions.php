<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['pagarme_subscription_id', 'pagarme_card_id']);
        });

        Schema::table('subscriptions_cycles', function (Blueprint $table) {
            $table->dropColumn('pagarme_cycle_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('pagarme_subscription_id')->nullable();
            $table->string('pagarme_card_id')->nullable();
        });

        Schema::table('subscriptions_cycles', function (Blueprint $table) {
            $table->string('pagarme_cycle_id')->nullable();
        });
    }
};
