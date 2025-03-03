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
        
        Schema::table('clients_purchases_items', function (Blueprint $table) {
            $table->renameColumn('item_type', 'action');
        });

        Schema::table('clients_purchases_items', function (Blueprint $table) {
            $table->string('type')->after('purchase_id');
        });

        Schema::table('clients_purchases_items', function (Blueprint $table) {
            $table->string('item_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_purchases_items', function (Blueprint $table) {
            //
        });
    }
};
