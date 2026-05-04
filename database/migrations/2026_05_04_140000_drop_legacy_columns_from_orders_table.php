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
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['type', 'key_id', 'previous_key_id', 'description'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'type')) {
                $table->string('type')->nullable()->after('pagarme_message');
            }

            if (!Schema::hasColumn('orders', 'key_id')) {
                $table->string('key_id')->nullable()->after('type');
            }

            if (!Schema::hasColumn('orders', 'previous_key_id')) {
                $table->unsignedBigInteger('previous_key_id')->nullable()->after('key_id');
            }

            if (!Schema::hasColumn('orders', 'description')) {
                $table->string('description')->nullable()->after('method');
            }
        });
    }
};
