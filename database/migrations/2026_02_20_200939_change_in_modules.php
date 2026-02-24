<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::where('id', '>', 0)->update(['pricing_type' => 'Preço Fixo']);
        Schema::table('modules', function (Blueprint $table) {
            $table->enum('pricing_type', ['Preço Fixo', 'Preço Por Uso'])->default('Preço Fixo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            //
        });
    }
};
