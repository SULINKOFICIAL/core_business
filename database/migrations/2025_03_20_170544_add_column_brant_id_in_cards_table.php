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
        Schema::table('clients_cards', function (Blueprint $table) {
            $table->dateTime('brand_tid_at')->nullable()->after('tokenization_id_at');
            $table->string('brand_tid')->nullable()->after('tokenization_id_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_cards', function (Blueprint $table) {
            //
        });
    }
};
