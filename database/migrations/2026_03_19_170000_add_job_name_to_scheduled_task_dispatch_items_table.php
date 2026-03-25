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
        Schema::table('scheduled_task_dispatch_items', function (Blueprint $table) {
            $table->string('job_name')->nullable()->after('client_id');
            $table->index(['dispatch_id', 'job_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_task_dispatch_items', function (Blueprint $table) {
            $table->dropIndex(['dispatch_id', 'job_name']);
            $table->dropColumn('job_name');
        });
    }
};
