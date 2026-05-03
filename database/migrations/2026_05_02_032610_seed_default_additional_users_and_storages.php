<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    private array $defaultAdditionalUsers = [1, 2, 3, 5, 10];

    private array $defaultAdditionalStorages = [10, 25, 50, 100, 250];

    public function up(): void
    {
        $seedUserId = DB::table('users')->orderBy('id')->value('id');

        if (!$seedUserId) {
            return;
        }

        $now = Carbon::now();

        foreach ($this->defaultAdditionalUsers as $quantity) {
            $exists = DB::table('additional_users')
                ->where('quantity', $quantity)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('additional_users')->insert([
                'quantity' => $quantity,
                'price' => 0,
                'status' => true,
                'filed_by' => null,
                'created_by' => $seedUserId,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->defaultAdditionalStorages as $quantity) {
            $exists = DB::table('additional_storages')
                ->where('quantity', $quantity)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('additional_storages')->insert([
                'quantity' => $quantity,
                'price' => 0,
                'status' => true,
                'filed_by' => null,
                'created_by' => $seedUserId,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('additional_users')
            ->where('price', 0)
            ->whereIn('quantity', $this->defaultAdditionalUsers)
            ->delete();

        DB::table('additional_storages')
            ->where('price', 0)
            ->whereIn('quantity', $this->defaultAdditionalStorages)
            ->delete();
    }
};
