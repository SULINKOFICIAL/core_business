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
        Schema::table('packages_modules', function (Blueprint $table) {
            $table->foreignId('module_pricing_tier_id')->nullable()->after('module_id')->constrained('module_pricing_tiers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages_modules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('module_pricing_tier_id');
        });
    }
};
