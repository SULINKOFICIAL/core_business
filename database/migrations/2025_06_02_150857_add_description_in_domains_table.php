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
        Schema::table('clients_domains', function (Blueprint $table) {
            $table->string('description')->nullable()->after('domain');
            $table->boolean('auto_generate')->default(false)->after('description');
            $table->boolean('status')->default(true)->after('auto_generate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients_domains', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('auto_generate');
            $table->dropColumn('status');
        });
    }
};
