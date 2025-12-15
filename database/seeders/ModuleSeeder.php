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
            'name' => 'Gestão',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Atendimento',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
            'value' => 20,
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Tarefas',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
            'value' => 20,
            'status' => 1,
            'created_by' => 2,
        ]);

        Module::create([
            'name' => 'Recursos Humanos',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Humanos',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Vendas, Produtos e Serviços',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem, Produtos e Serviços',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Marketing',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Streaming',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
            'value' => 20,
            'status' => 1,
            'created_by' => 1,
        ]);

        Module::create([
            'name' => 'Financias',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem',
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
