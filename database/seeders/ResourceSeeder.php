<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Resource::create([
            'name' => 'Grupo de usuários',
            'status' => 1,
            'created_by' => 1,
        ]);

        Resource::create([
            'name' => 'Anotações',
            'status' => 1,
            'created_by' => 2,
        ]);

        Resource::create([
            'name' => 'Personalização',
            'status' => 1,
            'created_by' => 2,
        ]);

        Resource::create([
            'name' => 'Notificações',
            'status' => 1,
            'created_by' => 1,
        ]);
    }
}
