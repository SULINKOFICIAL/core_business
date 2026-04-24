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
            $table->text('scopes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE tenants_integrations
            SET scopes = LEFT(scopes, 255)
            WHERE scopes IS NOT NULL AND CHAR_LENGTH(scopes) > 255
        ");

        Schema::table('tenants_integrations', function (Blueprint $table) {
            $table->string('scopes', 255)->nullable()->change();
        });
    }
};
