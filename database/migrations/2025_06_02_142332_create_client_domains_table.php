<?php

use App\Models\Client;
use App\Models\ClientDomain;
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
        Schema::create('clients_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('domain');
            $table->timestamps();
        });

        // Obtém clientes
        $clients = Client::all();

        foreach ($clients as $client) {
            ClientDomain::create([
                'client_id' => $client->id,
                'domain' => $client->domain,
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
        Schema::dropIfExists('client_domains');
    }
};
