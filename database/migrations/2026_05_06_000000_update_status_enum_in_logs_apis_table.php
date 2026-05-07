<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Passo 1: expande o enum para conter os valores antigos E os novos ao mesmo tempo
        Schema::table('logs_apis', function (Blueprint $table) {
            $table->enum('status', [
                'Pendente', 'Processado', 'Aguardando', 'Erro',
                'Recebido', 'Enviado', 'Falhou', 'Reprocessado', 'Ignorado', 'Duplicado',
            ])->default('Recebido')->change();
        });

        // Passo 2: migra os valores antigos para os novos equivalentes
        DB::table('logs_apis')->where('status', 'Pendente')->update(['status' => 'Recebido']);
        DB::table('logs_apis')->where('status', 'Aguardando')->update(['status' => 'Recebido']);
        DB::table('logs_apis')->where('status', 'Erro')->update(['status' => 'Falhou']);

        // Passo 3: remove os valores antigos do enum
        Schema::table('logs_apis', function (Blueprint $table) {
            $table->enum('status', [
                'Recebido',
                'Processado',
                'Enviado',
                'Falhou',
                'Reprocessado',
                'Ignorado',
                'Duplicado',
            ])->default('Recebido')->change();
        });
    }

    public function down(): void
    {
        Schema::table('logs_apis', function (Blueprint $table) {
            $table->enum('status', ['Pendente', 'Processado', 'Aguardando', 'Erro'])->default('Pendente')->change();
        });
    }
};
