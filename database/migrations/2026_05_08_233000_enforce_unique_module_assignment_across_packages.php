<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('packages_modules') || !Schema::hasTable('packages')) {
            return;
        }

        $moduleIds = DB::table('packages_modules as pm')
            ->join('packages as p', 'p.id', '=', 'pm.package_id')
            ->where('p.status', true)
            ->groupBy('pm.module_id')
            ->havingRaw('COUNT(DISTINCT pm.package_id) > 1')
            ->pluck('pm.module_id');

        foreach ($moduleIds as $moduleId) {
            $winnerPackage = DB::table('packages_modules as pm')
                ->join('packages as p', 'p.id', '=', 'pm.package_id')
                ->where('p.status', true)
                ->where('pm.module_id', $moduleId)
                ->orderBy('p.order')
                ->orderBy('p.id')
                ->select('pm.package_id')
                ->first();

            if (!$winnerPackage) {
                continue;
            }

            $deletedRows = DB::table('packages_modules')
                ->where('module_id', $moduleId)
                ->where('package_id', '!=', $winnerPackage->package_id)
                ->delete();

            if ($deletedRows > 0) {
                echo 'module_id=' . $moduleId . ' mantido em package_id=' . $winnerPackage->package_id . ', removidos=' . $deletedRows . PHP_EOL;
            }
        }
    }

    public function down(): void
    {
        // Saneamento irreversível.
    }
};

