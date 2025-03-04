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
        Schema::table('clients_purchases', function (Blueprint $table) {
            $table->integer('previous_key_id')->nullable()->after('client_id');
            $table->string('key_id')->after('client_id');
            $table->string('type')->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_purchases', function (Blueprint $table) {
            $table->dropColumn(['type', 'previous_key_id', 'key_id']);
        });
    }
};
