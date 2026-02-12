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
            $table->integer('client_provider_id')->nullable()->after('provider');
            $table->text('access_token')->nullable()->change();
            $table->string('temporary')->nullable()->after('external_account_id');
            $table->enum('status', ['active', 'expired', 'revoked', 'in_progress'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_integrations', function (Blueprint $table) {
            //
        });
    }
};
