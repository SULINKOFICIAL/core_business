<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('clients_packages_items')
            ->select(['id', 'item_id'])
            ->where(function ($query) {
                $query->whereNull('module_name')
                    ->orWhereNull('module_value')
                    ->orWhereNull('billing_type')
                    ->orWhereNull('payload');
            })
            ->orderBy('id')
            ->chunkById(200, function ($items) {
                foreach ($items as $item) {
                    $module = DB::table('modules')
                        ->select(['id', 'name', 'value', 'pricing_type'])
                        ->where('id', $item->item_id)
                        ->first();

                    if (!$module) {
                        continue;
                    }

                    DB::table('clients_packages_items')->where('id', $item->id)->update([
                        'module_name'  => $module->name,
                        'module_value' => $module->value,
                        'billing_type' => $module->pricing_type,
                        'payload'      => json_encode($module),
                        'updated_at'   => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backfill migration: no rollback.
    }
};
