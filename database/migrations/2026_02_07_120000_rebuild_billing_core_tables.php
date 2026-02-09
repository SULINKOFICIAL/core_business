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
        // Drop legacy or inconsistent tables first (children -> parents)
        Schema::dropIfExists('order_item_configurations');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders_items');
        Schema::dropIfExists('order_transactions');
        Schema::dropIfExists('orders_transactions');
        Schema::dropIfExists('client_subscription_items');
        Schema::dropIfExists('client_subscriptions');
        Schema::dropIfExists('clients_subscriptions');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');

            // Core flow
            $table->string('status')->default('draft');
            $table->string('currency', 3)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->json('pricing_snapshot')->nullable();
            $table->json('rules_snapshot')->nullable();

            // Legacy compatibility
            $table->string('type')->nullable();
            $table->string('key_id')->nullable();
            $table->unsignedBigInteger('previous_key_id')->nullable();
            $table->string('method')->nullable();
            $table->string('description')->nullable();

            // Lifecycle timestamps
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->dateTime('expired_at')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Canonical snapshot fields
            $table->string('item_type'); // module, package, credit, etc
            $table->string('action')->nullable(); // add, remove, upgrade, downgrade, etc
            $table->string('item_code')->nullable();
            $table->string('item_name_snapshot');
            $table->string('item_reference_type')->nullable();
            $table->unsignedBigInteger('item_reference_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_snapshot', 12, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->json('pricing_model_snapshot')->nullable();
            $table->json('rules_snapshot')->nullable();

            // Legacy compatibility
            $table->string('type')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_key')->nullable();
            $table->decimal('item_value', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });

        Schema::create('order_item_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('value_type')->nullable();
            $table->json('derived_pricing_effect')->nullable();
            $table->timestamps();
        });

        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('gateway_id')->nullable()->constrained('gateways');

            $table->string('gateway_code')->nullable();
            $table->string('external_transaction_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->nullable();

            // Legacy compatibility
            $table->string('method')->nullable();
            $table->dateTime('brand_tid_at')->nullable();
            $table->string('brand_tid')->nullable();
            $table->longText('response')->nullable();

            $table->json('raw_response_snapshot')->nullable();
            $table->dateTime('authorized_at')->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });

        Schema::create('clients_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('order_id')->nullable()->constrained('orders');

            // Legacy compatibility
            $table->foreignId('package_id')->nullable()->constrained('packages');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            // Canonical subscription fields
            $table->string('status')->default('active');
            $table->string('billing_cycle')->nullable();
            $table->dateTime('current_period_start')->nullable();
            $table->dateTime('current_period_end')->nullable();
            $table->dateTime('next_billing_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->dateTime('paused_at')->nullable();

            $table->timestamps();
        });

        Schema::create('client_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_subscription_id')->constrained('clients_subscriptions')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('modules');
            $table->string('module_code')->nullable();
            $table->string('status')->default('active');
            $table->json('current_config_snapshot')->nullable();
            $table->json('current_price_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_subscription_items');
        Schema::dropIfExists('clients_subscriptions');
        Schema::dropIfExists('order_transactions');
        Schema::dropIfExists('order_item_configurations');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
