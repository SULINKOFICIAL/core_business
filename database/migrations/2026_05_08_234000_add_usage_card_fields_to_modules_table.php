<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'usage_card_title')) {
                $table->string('usage_card_title')->nullable()->after('usage_label');
            }

            if (!Schema::hasColumn('modules', 'usage_card_subtitle')) {
                $table->string('usage_card_subtitle')->nullable()->after('usage_card_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'usage_card_subtitle')) {
                $table->dropColumn('usage_card_subtitle');
            }

            if (Schema::hasColumn('modules', 'usage_card_title')) {
                $table->dropColumn('usage_card_title');
            }
        });
    }
};

