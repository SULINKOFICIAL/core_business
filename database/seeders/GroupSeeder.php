<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Group::create([
            'name' => 'Recursos Básicos',
            'status' => 1,
            'created_by' => 1,
        ]);

        Group::create([
            'name' => 'CRM',
            'status' => 1,
            'created_by' => 2,
        ]);

        Group::create([
            'name' => 'Desenvolvimento',
            'status' => 1,
            'created_by' => 2,
        ]);

        Group::create([
            'name' => 'Marketing e Publicidade',
            'status' => 1,
            'created_by' => 1,
        ]);
    }
}