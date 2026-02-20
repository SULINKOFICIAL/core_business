<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normaliza valores legados no tipo de cobrança dos módulos
        DB::table('modules')
            ->whereIn('pricing_type', ['Por Uso', 'usage', 'Usage', 'USAGE'])
            ->update(['pricing_type' => 'Preço Por Uso']);

        DB::table('modules')
            ->whereIn('pricing_type', ['fixed', 'Fixed', 'FIXED'])
            ->update(['pricing_type' => 'Preço Fixo']);

        // Normaliza snapshots legados em itens de pedido quando a coluna existir
        if (Schema::hasColumn('order_items', 'item_billing_type')) {
            DB::table('order_items')
                ->whereIn('item_billing_type', ['Por Uso', 'usage', 'Usage', 'USAGE'])
                ->update(['item_billing_type' => 'Preço Por Uso']);

            DB::table('order_items')
                ->whereIn('item_billing_type', ['fixed', 'Fixed', 'FIXED'])
                ->update(['item_billing_type' => 'Preço Fixo']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sem rollback: normalização definitiva de dados legados
    }
};
