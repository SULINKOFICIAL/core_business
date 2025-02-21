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
        Schema::table('errors_micore', function (Blueprint $table) {
            $table->integer('status_code')->after('stack_trace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('errors_micore', function (Blueprint $table) {
            $table->dropColumn('status_code');
        });
    }
};
