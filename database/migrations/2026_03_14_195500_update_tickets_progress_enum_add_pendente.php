<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Atualiza o enum de progresso para o fluxo atual de tickets.
     */
    public function up(): void
    {
        // Converte valores antigos antes de remover o estado legado do enum.
        DB::statement("UPDATE tickets SET progress = 'em andamento' WHERE progress = 'aberto'");

        DB::statement("
            ALTER TABLE tickets
            MODIFY progress ENUM('pendente', 'em andamento', 'fechado')
            NOT NULL DEFAULT 'pendente'
        ");
    }

    /**
     * Restaura a definicao antiga do enum caso seja necessario voltar atras.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE tickets
            MODIFY progress ENUM('pendente', 'em andamento', 'aberto', 'fechado')
            NOT NULL DEFAULT 'aberto'
        ");
    }
};
