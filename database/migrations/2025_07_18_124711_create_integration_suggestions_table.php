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
        Schema::create('integration_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name');
            $table->text('description');
            $table->enum('progress', ['em andamento', 'aberto', 'fechado'])->default('aberto');
            $table->boolean('status')->default(true);
            $table->foreignId('filed_by')->nullable()->constrained('users');
            $table->foreignId('finished_by')->nullable()->constrained('users');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_suggestions');
    }
};