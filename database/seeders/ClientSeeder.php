<?php

namespace Database\Seeders;

use App\Http\Controllers\PackageController;
use App\Jobs\GenerateRenewalOrders;
use App\Models\Client;
use App\Models\ClientDomain;
use App\Models\Package;
use App\Services\OrderService;
use App\Models\User;
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
        $userId = User::query()->min('id') ?? 1;

        $client = Client::firstOrCreate(
            ['email' => 'micore@testes.com'],
            [
                'name'       => 'localhost',
                'token'      => 'abc_123',
                'created_by' => $userId,
            ]
        );

        ClientDomain::firstOrCreate(
            ['client_id' => $client->id, 'domain' => '127.0.0.1:8001'],
            ['description' => 'Testes']
        );

        // Adiciona o pacote inicial ao cliente via OrderService
        $package = Package::query()->first();
        if ($package) {
            $orderService = new OrderService();
            $orderResponse = $orderService->createOrder($client, $package);
            if (isset($orderResponse['order'])) {
                $orderService->confirmPaymentOrder($orderResponse['order']);
            }
        }
        
        // Adiciona o pacote "Começando" com 5 dias para testar a emissão de renovações.
        if(false){
            $package = Package::query()->skip(1)->first();
            if ($package) {
                $orderService = new OrderService();
                $orderResponse = $orderService->createOrder($client, $package);
                if (isset($orderResponse['order'])) {
                    $orderService->confirmPaymentOrder($orderResponse['order']);
                }
            }
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
