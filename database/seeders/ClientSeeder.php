<?php

namespace Database\Seeders;

use App\Http\Controllers\PackageController;
use App\Jobs\GenerateRenewalOrders;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Cria usuário local para testes
        Client::create([
            'name'       => 'localhost',
            'email'      => 'micore@testes.com',
            'domain'     => '127.0.0.1:8001',
            'token'      => '111',
            'created_by' => 1,
        ]);

        // Adiciona o pacote inicial ao cliente
        $request = new Request(['package_id' => 1]);
        app(PackageController::class)->assign($request, 1);
        
        // Adiciona o pacote "Começando" com 5 dias para testar a emissão de renovações.
        if(false){
            $request = new Request(['package_id' => 2]);
            app(PackageController::class)->assign($request, 1);
        }

        /* 
        Client::create([
            'name' => 'Coca Cola',
            'email' => 'coca@cocacola.com.br',
            'domain' => '127.0.0.1:8001',
            'token' => '111',
            'created_by' => 1,
        ]);
        
        Client::create([
            'name' => 'Porsche',
            'email' => 'porsche@porscheerp.com.br',
            'domain' => '127.0.0.1:8002',
            'token' => '222',
            'created_by' => 1,
        ]); */

    }
}
