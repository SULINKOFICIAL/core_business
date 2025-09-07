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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->string('category');
            $table->enum('priority', ['high', 'medium', 'low'])->default('low');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('cta_url', 1000)->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('filed_by')->nullable(); 
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
