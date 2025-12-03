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
        Schema::table('clients_integrations', function (Blueprint $table) {
            $table->enum('type', ['whatsapp', 'instagram', 'facebook', 'mercado_livre'])->nullable()->after('provider');
            $table->string('scopes')->nullable()->after('provider');
            $table->dropColumn('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_integrations', function (Blueprint $table) {
            $table->dropColumn('scopes');
            $table->dropColumn('type');
            $table->string('refresh_token')->nullable()->after('access_token');
        });
    }
};
