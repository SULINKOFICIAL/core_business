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
        /* Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('type_instalation', 'type_installation');
        }); */

        Schema::table('clients', function (Blueprint $table) {
            $table->enum('type_installation', ['dedicated', 'shared'])->default('shared')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
