<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sector::create([
            'name' => 'Financeiro',
            'status' => 1,
            'created_by' => 1,
        ]);

        Sector::create([
            'name' => 'Usuários',
            'status' => 1,
            'created_by' => 2,
        ]);

        Sector::create([
            'name' => 'Blog',
            'status' => 1,
            'created_by' => 2,
        ]);

        Sector::create([
            'name' => 'Vendas',
            'status' => 1,
            'created_by' => 1,
        ]);
    }
}
