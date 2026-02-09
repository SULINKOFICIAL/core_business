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
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->dateTime('redeemed_at')->nullable();
            $table->decimal('amount_discounted', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->string('code_snapshot');
            $table->string('type_snapshot');
            $table->decimal('value_snapshot', 12, 2)->nullable();
            $table->unsignedInteger('trial_months_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
