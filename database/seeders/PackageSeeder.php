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
            'name'          => 'Teste Gratuíto',
            'size_storage'  => 1073741824 * 1,
            'free'          => true,
            'value'         => 0,
            'duration_days' => 5,
            'order'         => 1,
            'created_by'    => 1,
        ]);
        
        Package::create([
            'name'          => 'Começando',
            'size_storage'  => 1073741824 * 5,
            'value'         => 1,
            'duration_days' => 5, // 5 dias para testar renovação
            'order'         => 1,
            'created_by'    => 1,
        ]);

        Package::create([
            'name' => 'Avançado',
            'size_storage'  => 1073741824 * 10,
            'status' => 1,
            'value' => 2,
            'duration_days' => 30,
            'order' => 1,
            'created_by' => 2,
        ]);

        Package::create([
            'name' => 'Empresas',
            'size_storage'  => 1073741824 * 15,
            'status' => 1,
            'value' => 3,
            'duration_days' => 30,
            'order' => 1,
            'created_by' => 2,
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