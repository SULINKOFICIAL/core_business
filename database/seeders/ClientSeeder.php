<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Cria usuário local para testes
        $userId = User::query()->min('id') ?? 1;

        $tenant = Tenant::firstOrCreate(
            ['email' => 'micore@testes.com'],
            [
                'name'       => 'localhost',
                'token'      => 'abc_123',
                'created_by' => $userId,
            ]
        );

        TenantDomain::firstOrCreate(
            ['tenant_id' => $tenant->id, 'domain' => '127.0.0.1:8001'],
            ['description' => 'Testes']
        );

        /* 
        Tenant::create([
            'name' => 'Coca Cola',
            'email' => 'coca@cocacola.com.br',
            'domain' => '127.0.0.1:8001',
            'token' => '111',
            'created_by' => 1,
        ]);
        
        Tenant::create([
            'name' => 'Porsche',
            'email' => 'porsche@porscheerp.com.br',
            'domain' => '127.0.0.1:8002',
            'token' => '222',
            'created_by' => 1,
        ]); */

    }
}
