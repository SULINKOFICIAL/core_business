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
            'token' => 'gk3RawCERe3uk6EmSdtJOMFtvnRQGS7N16M0l3K98c012484',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Coca Cola',
            'domain' => 'cocacola.com.br',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Porsche',
            'domain' => 'porscheerp.com.br',
            'created_by' => 1,
        ]);

        Client::create([
            'name' => 'Stanley',
            'domain' => 'stanley1913.com.br',
            'created_by' => 1,
        ]);
    }
}