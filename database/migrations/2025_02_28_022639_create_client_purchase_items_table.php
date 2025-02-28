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
        Schema::create('clients_purchases_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('client_purchases');
            $table->string('item_type');
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('item_value', 10, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_purchases_items');
    }
};
