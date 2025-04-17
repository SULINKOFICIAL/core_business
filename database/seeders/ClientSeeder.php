<?php

namespace Database\Seeders;

use App\Http\Controllers\PackageController;
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
       /*  Client::create([
            'name' => 'Sulink',
            'domain' => 'sulink.com.br',
            'token' => '123',
            'package_id' => 1,
            'created_by' => 1,
        ]); */

        Client::create([
            'name' => 'localhost',
            'email' => 'stanley@gmail.com',
            'domain' => '127.0.0.1:8000',
            'token' => '111',
            'created_by' => 1,
        ]);


        // Simula solicitação de troca de pacote
        $request = new Request(['package_id' => 1]);
        app(PackageController::class)->assign($request, 1);
        $request = new Request(['package_id' => 2]);
        app(PackageController::class)->assign($request, 1);

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
