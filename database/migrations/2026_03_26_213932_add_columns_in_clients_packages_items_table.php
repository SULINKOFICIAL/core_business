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
        Schema::table('clients_packages_items', function (Blueprint $table) {
            $table->string('module_name')->nullable()->after('item_id');
            $table->decimal('module_value', 10, 2)->nullable()->after('module_name');
            $table->string('billing_type')->nullable()->after('module_value');
            $table->json('payload')->nullable()->after('billing_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_packages_items', function (Blueprint $table) {
            $table->dropColumn(['module_name', 'module_value', 'billing_type', 'payload']);
        });
    }
};
