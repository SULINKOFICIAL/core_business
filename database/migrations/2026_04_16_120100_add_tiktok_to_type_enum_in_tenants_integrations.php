<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants_integrations', function (Blueprint $table) {
            $table->enum('type', ['whatsapp', 'instagram', 'facebook', 'mercado_livre', 'tiktok'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tenants_integrations')
            ->where('type', 'tiktok')
            ->update(['type' => null]);

        Schema::table('tenants_integrations', function (Blueprint $table) {
            $table->enum('type', ['whatsapp', 'instagram', 'facebook', 'mercado_livre'])->nullable()->change();
        });
    }
};
