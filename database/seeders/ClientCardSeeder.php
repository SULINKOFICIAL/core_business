<?php

namespace Database\Seeders;

use App\Models\ClientCard;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = Client::query()->min('id');
        if (!$clientId) {
            return;
        }

        ClientCard::create([
            'client_id'         => $clientId,
            'name'              => 'John Snow',
            'number'            => 5448280000000007,
            'expiration_month'  => 1,
            'expiration_year'   => 2028,
        ]);
    }
}
