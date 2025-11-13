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
        Schema::create('logs_apis', function (Blueprint $table) {
            $table->id();
            $table->enum('api', ['Meta', 'Mercado Livre']);
            $table->longText('json');
            $table->boolean('reprocessed')->nullable();
            $table->bigInteger('new_log_id',)->nullable();
            $table->enum('status', ['Pendente', 'Processado', 'Aguardando', 'Erro'])->default('Pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_apis');
    }
};
