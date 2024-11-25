<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Cria usuário Jeandreo
        User::create([
            'name' => 'Jeandreo Furquim',
            'email' => 'jeandreo@sulink.com.br',
            'password' => Hash::make('@Sucesso1243'),
        ]);
    }
}
