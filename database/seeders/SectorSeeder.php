<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Module::create([
            'name' => 'Financeiro',
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Usuários',
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Blog',
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Vendas',
            'status' => 1,
            'created_by' => 1,
        ]);
    }
}
