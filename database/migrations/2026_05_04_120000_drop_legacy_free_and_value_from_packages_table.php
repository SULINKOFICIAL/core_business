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
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'free')) {
                $table->dropColumn('free');
            }

            if (Schema::hasColumn('packages', 'value')) {
                $table->dropColumn('value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'free')) {
                $table->boolean('free')->default(false)->after('name');
            }

            if (!Schema::hasColumn('packages', 'value')) {
                $table->decimal('value', 10, 2)->default(0)->after('name');
            }
        });
    }
};
