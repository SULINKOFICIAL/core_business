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
        // Cria a tabela base para auditoria simples de sucesso e erro dos envios.
        Schema::create('email_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->string('template')->default('emails.simple');
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Indexa consultas por destinatário e status para histórico administrativo.
            $table->index(['recipient_email', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a tabela de log caso a feature precise ser revertida.
        Schema::dropIfExists('email_dispatch_logs');
    }
};
