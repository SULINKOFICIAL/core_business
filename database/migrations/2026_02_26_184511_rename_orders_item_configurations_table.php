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
        if (!Schema::hasTable('clients_packages_items_configurations')) {
            Schema::create('clients_packages_items_configurations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('clients_packages_items')->cascadeOnDelete();
                $table->string('key');
                $table->text('value')->nullable();
                $table->string('value_type')->nullable();
                $table->json('derived_pricing_effect')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_packages_items_configurations');
    }
};
