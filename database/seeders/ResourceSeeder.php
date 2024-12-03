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
            'slug' => 'grupo-de-usuarios',
            'status' => 1,
            'created_by' => 1,
        ]);

        Resource::create([
            'name' => 'Anotações',
            'slug' => 'anotacoes',
            'status' => 1,
            'created_by' => 2,
        ]);

        Resource::create([
            'name' => 'Personalização',
            'slug' => 'personalizacao',
            'status' => 1,
            'created_by' => 2,
        ]);

        Resource::create([
            'name' => 'Notificações',
            'slug' => 'notificacoes',
            'status' => 1,
            'created_by' => 1,
        ]);
    }
}
