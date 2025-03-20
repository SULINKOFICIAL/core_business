<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Coca Cola',
            'email' => 'coca@cocacola.com.br',
            'domain' => 'cocacola.com.br',
            'token' => '111',
            'created_by' => 1,
        ]);
        
        Client::create([
            'name' => 'Porsche',
            'email' => 'porsche@porscheerp.com.br',
            'domain' => 'porscheerp.com.br',
            'token' => '222',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Stanley',
            'email' => 'stanley@gmail.com',
            'domain' => 'stanley.com.br',
            'token' => '333',
            'created_by' => 1,
        ]);
    }
}
