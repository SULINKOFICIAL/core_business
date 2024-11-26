<?php

namespace Database\Seeders;

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
        User::create([
            'name' => 'Jeandreo Furquim',
            'email' => 'jeandreo@sulink.com.br',
            'password' => Hash::make('@Sucesso1243'),
        ]);

        Client::create([
            'name' => 'Sulink',
            'domain' => 'www.sulink.com.br',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Coca Cola',
            'domain' => 'www.cocacola.com.br',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Porsche',
            'domain' => 'www.porscheerp.com.br',
            'created_by' => 1,
        ]);
    }
}
