<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Module::create([
            'name' => 'Financeiro',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Usuários',
            'value' => 20,
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Blog',
            'value' => 20,
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Vendas',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);
        
        // Obtém os recuros e adiciona aos grupos
        $modules = Module::get();
        $groups = Group::get();

        // Adiciona os grupos
        foreach ($modules as $module) {
            foreach ($groups as $group) {
                ModuleGroup::create([
                    'module_id' => $module->id,
                    'group_id' => $group->id,
               ]);
            }
        }
        
    }
}
