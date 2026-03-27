<?php

use App\Models\ClientPackageItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ClientPackageItem::query()
            ->select(['id', 'item_id'])
            ->with('item')
            ->where(function ($query) {
                $query->whereNull('module_name')
                    ->orWhereNull('module_value')
                    ->orWhereNull('billing_type')
                    ->orWhereNull('payload');
            })
            ->orderBy('id')
            ->chunkById(200, function ($items) {
                foreach ($items as $item) {
                    $module = $item->item;

                    if (!$module) {
                        continue;
                    }

                    $item->fill([
                        'module_name'  => $module->name,
                        'module_value' => $module->value,
                        'billing_type' => $module->pricing_type,
                        'payload'      => json_encode($module),
                    ]);
                    $item->save();
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
