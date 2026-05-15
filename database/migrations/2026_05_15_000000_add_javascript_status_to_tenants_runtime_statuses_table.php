<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o eixo de status do build de Javascript na listagem de tenants.
     */
    public function up(): void
    {
        Schema::table('tenants_runtime_statuses', function (Blueprint $table) {
            $table->boolean('js_last_version')->default(false)->after('sp_error');
            $table->longText('js_error')->nullable()->after('js_last_version');
        });
    }

    /**
     * Remove o eixo de status do build de Javascript.
     */
    public function down(): void
    {
        Schema::table('tenants_runtime_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'js_last_version',
                'js_error',
            ]);
        });
    }
};
