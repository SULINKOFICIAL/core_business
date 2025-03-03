<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Package;
use App\Models\PackageModule;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::create([
            'name'          => 'Free Trial',
            'value'         => 89.9,
            'duration_days' => 30,
            'order'         => 1,
            'created_by'    => 1,
        ]);
        
        Package::create([
            'name'          => 'Premium Trial',
            'value'         => 149,
            'duration_days' => 30,
            'order'         => 1,
            'created_by'    => 1,
        ]);

        Package::create([
            'name' => 'Vendas Full',
            'status' => 1,
            'value' => '250.00',
            'duration_days' => 30,
            'order' => 1,
            'created_by' => 2,
        ]);

        Package::create([
            'name' => 'Financeiro',
            'status' => 1,
            'value' => '10.00',
            'duration_days' => 30,
            'order' => 1,
            'created_by' => 2,
        ]);

        Package::create([
            'name' => 'Atendimento ao Cliente',
            'value' => '199.90',
            'duration_days' => 30,
            'status' => 1,
            'order' => 1,
            'created_by' => 1,
        ]);
 
        // Obtém os recuros e adiciona aos grupos
        $packages = Package::get();
        $modules = Module::get();

        // Adiciona os grupos
        foreach ($packages as $key => $package) {
            foreach ($modules->take(count($modules) - 1) as $module) {
                PackageModule::create([
                    'module_id' => $module->id,
                    'package_id' => $package->id,
                    'created_by' => 1,
                ]);
            }
        }


        // Randomiza módulos
        if($key <= 1 || rand(true, false)){
        }

    }
}