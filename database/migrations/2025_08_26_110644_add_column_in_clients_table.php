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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('table_user')->nullable()->after('table');
            $table->json('first_user')->nullable()->after('table_password');
            $table->enum('install', ['Cadastro', 'Domínio', 'Banco de Dados', 'Usuário e Token', 'Concluído'])->default('Cadastro')->after('first_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('table_user');
            $table->dropColumn('first_user');
            $table->dropColumn('install');
        });
    }
};
