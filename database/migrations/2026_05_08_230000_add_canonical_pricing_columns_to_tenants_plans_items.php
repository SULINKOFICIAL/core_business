<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants_plans_items')) {
            return;
        }

        Schema::table('tenants_plans_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants_plans_items', 'item_type')) {
                $table->string('item_type')->default('module')->after('item_id');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable()->after('module_value');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'applied_price')) {
                $table->decimal('applied_price', 12, 2)->nullable()->after('base_price');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->nullable()->after('applied_price');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'discount_percent')) {
                $table->decimal('discount_percent', 8, 3)->nullable()->after('discount_amount');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'pricing_source')) {
                $table->string('pricing_source')->nullable()->after('discount_percent');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'module_pricing_tier_id')) {
                $table->unsignedBigInteger('module_pricing_tier_id')->nullable()->after('pricing_source');
            }

            if (!Schema::hasColumn('tenants_plans_items', 'usage_limit')) {
                $table->integer('usage_limit')->nullable()->after('module_pricing_tier_id');
            }
        });

        $items = DB::table('tenants_plans_items')
            ->select('id', 'package_id', 'module_value', 'base_price', 'applied_price', 'discount_amount', 'discount_percent', 'pricing_source', 'item_type')
            ->get();

        foreach ($items as $item) {
            $basePrice = $item->base_price;
            $appliedPrice = $item->applied_price;

            if ($basePrice === null) {
                $basePrice = $item->module_value ?? 0;
            }

            if ($appliedPrice === null) {
                $appliedPrice = $item->module_value ?? $basePrice ?? 0;
            }

            $discountAmount = $item->discount_amount;
            if ($discountAmount === null) {
                $discountAmount = max(0, floatval($basePrice) - floatval($appliedPrice));
            }

            $discountPercent = $item->discount_percent;
            if ($discountPercent === null) {
                if (floatval($basePrice) > 0 && floatval($appliedPrice) < floatval($basePrice)) {
                    $discountPercent = round(((floatval($basePrice) - floatval($appliedPrice)) / floatval($basePrice)) * 100, 3);
                } else {
                    $discountPercent = 0;
                }
            }

            $pricingSource = $item->pricing_source;
            if ($pricingSource === null) {
                $pricingSource = $item->package_id ? 'package' : 'manual';
            }

            $itemType = $item->item_type;
            if ($itemType === null || $itemType === '') {
                $itemType = 'module';
            }

            DB::table('tenants_plans_items')
                ->where('id', $item->id)
                ->update([
                    'item_type' => $itemType,
                    'base_price' => $basePrice,
                    'applied_price' => $appliedPrice,
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPercent,
                    'pricing_source' => $pricingSource,
                ]);
        }

        $configs = DB::table('tenants_plans_items_configurations')
            ->where('key', 'usage')
            ->select('item_id', 'value')
            ->get();

        foreach ($configs as $config) {
            DB::table('tenants_plans_items')
                ->where('id', $config->item_id)
                ->whereNull('usage_limit')
                ->update([
                    'usage_limit' => intval($config->value),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenants_plans_items')) {
            return;
        }

        Schema::table('tenants_plans_items', function (Blueprint $table) {
            if (Schema::hasColumn('tenants_plans_items', 'usage_limit')) {
                $table->dropColumn('usage_limit');
            }

            if (Schema::hasColumn('tenants_plans_items', 'module_pricing_tier_id')) {
                $table->dropColumn('module_pricing_tier_id');
            }

            if (Schema::hasColumn('tenants_plans_items', 'pricing_source')) {
                $table->dropColumn('pricing_source');
            }

            if (Schema::hasColumn('tenants_plans_items', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }

            if (Schema::hasColumn('tenants_plans_items', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('tenants_plans_items', 'applied_price')) {
                $table->dropColumn('applied_price');
            }

            if (Schema::hasColumn('tenants_plans_items', 'base_price')) {
                $table->dropColumn('base_price');
            }

            if (Schema::hasColumn('tenants_plans_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });
    }
};

