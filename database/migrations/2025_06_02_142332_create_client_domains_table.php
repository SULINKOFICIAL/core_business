<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('domain');
            $table->timestamps();
        });

        // Obtém clientes
        $clients = DB::table('clients')
            ->select(['id', 'domain'])
            ->whereNotNull('domain')
            ->get();

        foreach ($clients as $client) {
            DB::table('clients_domains')->insert([
                'client_id' => $client->id,
                'domain' => $client->domain,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('domain');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_domains');
    }
};
