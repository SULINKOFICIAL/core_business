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
        Schema::create('scheduled_task_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->json('job_data')->nullable();
            $table->enum('source', ['scheduler', 'manual'])->default('scheduler');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_clients')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['job_name', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_dispatches');
    }
};
