<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateOrdersTableUp();
        $this->migrateTransactionsTableUp();
    }

    public function down(): void
    {
        $this->migrateOrdersTableDown();
        $this->migrateTransactionsTableDown();
    }

    private function migrateOrdersTableUp(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'provider')) {
                $table->string('provider')->nullable()->after('status');
            }

            if (!Schema::hasColumn('orders', 'provider_method')) {
                $table->string('provider_method')->nullable()->after('provider');
            }

            if (!Schema::hasColumn('orders', 'provider_message')) {
                $table->text('provider_message')->nullable()->after('provider_method');
            }
        });

        if (Schema::hasColumn('orders', 'method')) {
            DB::statement("UPDATE orders SET provider_method = method WHERE provider_method IS NULL AND method IS NOT NULL");
        }

        if (Schema::hasColumn('orders', 'pagarme_message')) {
            DB::statement("UPDATE orders SET provider_message = pagarme_message WHERE provider_message IS NULL AND pagarme_message IS NOT NULL");
            DB::statement("UPDATE orders SET provider = 'pagarme' WHERE provider IS NULL AND pagarme_message IS NOT NULL");

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('pagarme_message');
            });
        }
    }

    private function migrateOrdersTableDown(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'provider_message')) {
                $table->dropColumn('provider_message');
            }

            if (Schema::hasColumn('orders', 'provider_method')) {
                $table->dropColumn('provider_method');
            }

            if (Schema::hasColumn('orders', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }

    private function migrateTransactionsTableUp(): void
    {
        $tableName = 'orders_transactions';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'provider')) {
                $table->string('provider')->nullable()->after('status');
            }

            if (!Schema::hasColumn($tableName, 'provider_method')) {
                $table->string('provider_method')->nullable()->after('provider');
            }

            if (!Schema::hasColumn($tableName, 'provider_transaction_id')) {
                $table->string('provider_transaction_id')->nullable()->after('subscription_id');
            }
        });

        if (Schema::hasColumn($tableName, 'method')) {
            DB::statement("UPDATE {$tableName} SET provider_method = method WHERE provider_method IS NULL AND method IS NOT NULL");
        }

        if (Schema::hasColumn($tableName, 'pagarme_transaction_id')) {
            DB::statement("UPDATE {$tableName} SET provider_transaction_id = pagarme_transaction_id WHERE provider_transaction_id IS NULL AND pagarme_transaction_id IS NOT NULL");
            DB::statement("UPDATE {$tableName} SET provider = 'pagarme' WHERE provider IS NULL AND pagarme_transaction_id IS NOT NULL");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('pagarme_transaction_id');
            });
        }
    }

    private function migrateTransactionsTableDown(): void
    {
        $tableName = 'orders_transactions';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'provider_transaction_id')) {
                $table->dropColumn('provider_transaction_id');
            }

            if (Schema::hasColumn($tableName, 'provider_method')) {
                $table->dropColumn('provider_method');
            }

            if (Schema::hasColumn($tableName, 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
