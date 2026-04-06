<?php

namespace Database\Seeders;

use App\Models\TenantCard;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = Tenant::query()->min('id');
        if (!$tenantId) {
            return;
        }

        TenantCard::create([
            'tenant_id'         => $tenantId,
            'name'              => 'John Snow',
            'number'            => 5448280000000007,
            'expiration_month'  => 1,
            'expiration_year'   => 2028,
        ]);
    }
}
