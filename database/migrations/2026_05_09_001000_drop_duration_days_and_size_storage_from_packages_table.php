<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('packages')) {
            return;
        }

        $hasDurationDays = Schema::hasColumn('packages', 'duration_days');
        $hasSizeStorage = Schema::hasColumn('packages', 'size_storage');

        if (!$hasDurationDays && !$hasSizeStorage) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) use ($hasDurationDays, $hasSizeStorage) {
            if ($hasDurationDays) {
                $table->dropColumn('duration_days');
            }

            if ($hasSizeStorage) {
                $table->dropColumn('size_storage');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('packages')) {
            return;
        }

        $hasDurationDays = Schema::hasColumn('packages', 'duration_days');
        $hasSizeStorage = Schema::hasColumn('packages', 'size_storage');

        if ($hasDurationDays && $hasSizeStorage) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) use ($hasDurationDays, $hasSizeStorage) {
            if (!$hasDurationDays) {
                $table->unsignedInteger('duration_days')->default(30)->after('popular');
            }

            if (!$hasSizeStorage) {
                $table->unsignedBigInteger('size_storage')->default(5368709120)->after('duration_days');
            }
        });
    }
};
