<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::create([
            'name' => 'Pacote Básico',
            'status' => 1,
            'value' => '100.00',
            'order' => 1,
            'created_by' => 1,
        ]);

        Package::create([
            'name' => 'Vendas Full',
            'status' => 1,
            'value' => '250.00',
            'order' => 1,
            'created_by' => 2,
        ]);

        Package::create([
            'name' => 'Financeiro',
            'status' => 1,
            'value' => '10.00',
            'order' => 1,
            'created_by' => 2,
        ]);

        Package::create([
            'name' => 'Atendimento ao Cliente',
            'value' => '199.90',
            'status' => 1,
            'order' => 1,
            'created_by' => 1,
        ]);


    }
}