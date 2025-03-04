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
        Client::create([
            'name' => 'Sulink',
            'domain' => 'sulink.com.br',
            'token' => '123',
            // 'package_id' => 1,
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Coca Cola',
            'domain' => 'cocacola.com.br',
            'token' => '1234',
            // 'package_id' => 1,
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Porsche',
            'domain' => 'porscheerp.com.br',
            'token' => '12345',
            // 'package_id' => 1,
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Stanley',
            'domain' => 'stanley1913.com.br',
            'token' => '123456',
            // 'package_id' => 1,
            'created_by' => 1,
        ]);
    }
}
