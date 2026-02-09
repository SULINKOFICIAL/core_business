<?php

namespace Database\Seeders;

use App\Jobs\GenerateRenewalOrders;
use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Cria usuário Jeandreo
        User::firstOrCreate(
            ['email' => 'ramon@sulink.com.br'],
            ['name' => 'Ramon Piekarski', 'password' => Hash::make('290192')]
        );

        User::firstOrCreate(
            ['email' => 'jeandreo@sulink.com.br'],
            ['name' => 'Jeandreo Furquim', 'password' => Hash::make('@Sucesso1243')]
        );

        // Cria usuário Cauã
        User::firstOrCreate(
            ['email' => 'caua.teixeira@sulink.com.br'],
            ['name' => 'Cauã Teixeira', 'password' => Hash::make('@Ca11924180')]
        );

        // Seeder para recursos
        $this->call(ResourceSeeder::class);

        // Seeder para grupo de recursos
        $this->call(GroupSeeder::class);

        // Seeder para módulos
        $this->call(ModuleSeeder::class);

        // Seeder para pacotes
        $this->call(PackageSeeder::class);

        // Seeder para clientes
        $this->call(ClientSeeder::class);

        // Seeder para cartões de clientes
        $this->call(ClientCardSeeder::class);

        // Seeder para fluxo completo de pedidos/assinaturas
        $this->call(OrderFlowSeeder::class);

    }
}
