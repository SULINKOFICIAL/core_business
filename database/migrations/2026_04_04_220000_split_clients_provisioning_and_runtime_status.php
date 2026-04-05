<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_provisionings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->string('table')->nullable();
            $table->string('table_user')->nullable();
            $table->string('table_password')->nullable();
            $table->json('first_user')->nullable();
            $table->enum('install', [
                'subdomain',
                'database',
                'user_token',
                'modules',
                'finalizing',
                'completed',
            ])->default('subdomain');
            $table->timestamps();
        });

        Schema::create('client_runtime_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->boolean('db_last_version')->default(true);
            $table->longText('db_error')->nullable();
            $table->boolean('git_last_version')->default(false);
            $table->longText('git_error')->nullable();
            $table->boolean('sp_last_version')->default(false);
            $table->longText('sp_error')->nullable();
            $table->timestamps();
        });

        $clients = DB::table('clients')
            ->select([
                'id',
                'table',
                'table_user',
                'table_password',
                'first_user',
                'install',
                'db_last_version',
                'db_error',
                'git_last_version',
                'git_error',
                'sp_last_version',
                'sp_error',
            ])
            ->orderBy('id')
            ->get();

        $now = now();

        foreach ($clients as $client) {
            DB::table('client_provisionings')->insert([
                'client_id' => $client->id,
                'table' => $client->table,
                'table_user' => $client->table_user,
                'table_password' => $client->table_password,
                'first_user' => $client->first_user,
                'install' => match ((int) ($client->install ?? 1)) {
                    1 => 'subdomain',
                    2 => 'database',
                    3 => 'user_token',
                    4 => 'modules',
                    5 => 'finalizing',
                    default => 'completed',
                },
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('client_runtime_statuses')->insert([
                'client_id' => $client->id,
                'db_last_version' => (bool) ($client->db_last_version ?? true),
                'db_error' => $client->db_error,
                'git_last_version' => (bool) ($client->git_last_version ?? false),
                'git_error' => $client->git_error,
                'sp_last_version' => (bool) ($client->sp_last_version ?? false),
                'sp_error' => $client->sp_error,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'table',
                'table_user',
                'table_password',
                'first_user',
                'install',
                'db_last_version',
                'db_error',
                'git_last_version',
                'git_error',
                'sp_last_version',
                'sp_error',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('table')->nullable()->after('logo');
            $table->string('table_user')->nullable()->after('table');
            $table->string('table_password')->nullable()->after('table_user');
            $table->json('first_user')->nullable()->after('table_password');
            $table->integer('install')->default(1)->nullable()->after('first_user');
            $table->boolean('db_last_version')->default(true)->after('token');
            $table->longText('db_error')->nullable()->after('db_last_version');
            $table->boolean('git_last_version')->default(false)->after('db_error');
            $table->longText('git_error')->nullable()->after('git_last_version');
            $table->boolean('sp_last_version')->default(false)->after('git_error');
            $table->longText('sp_error')->nullable()->after('sp_last_version');
        });

        $provisionings = DB::table('client_provisionings')
            ->select(['client_id', 'table', 'table_user', 'table_password', 'first_user', 'install'])
            ->get()
            ->keyBy('client_id');

        $statuses = DB::table('client_runtime_statuses')
            ->select([
                'client_id',
                'db_last_version',
                'db_error',
                'git_last_version',
                'git_error',
                'sp_last_version',
                'sp_error',
            ])
            ->get()
            ->keyBy('client_id');

        $clients = DB::table('clients')->select(['id'])->get();

        foreach ($clients as $client) {
            $provisioning = $provisionings->get($client->id);
            $status = $statuses->get($client->id);

            DB::table('clients')
                ->where('id', $client->id)
                ->update([
                    'table' => $provisioning->table ?? null,
                    'table_user' => $provisioning->table_user ?? null,
                    'table_password' => $provisioning->table_password ?? null,
                    'first_user' => $provisioning->first_user ?? null,
                    'install' => match ($provisioning->install ?? 'subdomain') {
                        'subdomain' => 1,
                        'database' => 2,
                        'user_token' => 3,
                        'modules' => 4,
                        'finalizing' => 5,
                        default => 6,
                    },
                    'db_last_version' => $status->db_last_version ?? true,
                    'db_error' => $status->db_error ?? null,
                    'git_last_version' => $status->git_last_version ?? false,
                    'git_error' => $status->git_error ?? null,
                    'sp_last_version' => $status->sp_last_version ?? false,
                    'sp_error' => $status->sp_error ?? null,
                ]);
        }

        Schema::dropIfExists('client_runtime_statuses');
        Schema::dropIfExists('client_provisionings');
    }
};
