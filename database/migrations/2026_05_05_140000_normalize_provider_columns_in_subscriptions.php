<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateSubscriptions();
        $this->migrateSubscriptionCycles();
    }

    public function down(): void
    {
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (Schema::hasColumn('subscriptions', 'provider_card_id')) {
                    $table->dropColumn('provider_card_id');
                }

                if (Schema::hasColumn('subscriptions', 'provider_subscription_id')) {
                    $table->dropColumn('provider_subscription_id');
                }

                if (Schema::hasColumn('subscriptions', 'provider')) {
                    $table->dropColumn('provider');
                }
            });
        }

        if (Schema::hasTable('subscriptions_cycles')) {
            Schema::table('subscriptions_cycles', function (Blueprint $table) {
                if (Schema::hasColumn('subscriptions_cycles', 'provider_cycle_id')) {
                    $table->dropColumn('provider_cycle_id');
                }

                if (Schema::hasColumn('subscriptions_cycles', 'provider')) {
                    $table->dropColumn('provider');
                }
            });
        }
    }

    private function migrateSubscriptions(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'provider')) {
                $table->string('provider')->nullable()->after('order_id');
            }

            if (!Schema::hasColumn('subscriptions', 'provider_subscription_id')) {
                $table->string('provider_subscription_id')->nullable()->after('provider');
            }

            if (!Schema::hasColumn('subscriptions', 'provider_card_id')) {
                $table->string('provider_card_id')->nullable()->after('provider_subscription_id');
            }
        });

        if (Schema::hasColumn('subscriptions', 'pagarme_subscription_id')) {
            DB::statement("UPDATE subscriptions SET provider_subscription_id = pagarme_subscription_id WHERE provider_subscription_id IS NULL AND pagarme_subscription_id IS NOT NULL");
            DB::statement("UPDATE subscriptions SET provider = 'pagarme' WHERE provider IS NULL AND pagarme_subscription_id IS NOT NULL");
        }

        if (Schema::hasColumn('subscriptions', 'pagarme_card_id')) {
            DB::statement("UPDATE subscriptions SET provider_card_id = pagarme_card_id WHERE provider_card_id IS NULL AND pagarme_card_id IS NOT NULL");
        }
    }

    private function migrateSubscriptionCycles(): void
    {
        if (!Schema::hasTable('subscriptions_cycles')) {
            return;
        }

        Schema::table('subscriptions_cycles', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions_cycles', 'provider')) {
                $table->string('provider')->nullable()->after('subscription_id');
            }

            if (!Schema::hasColumn('subscriptions_cycles', 'provider_cycle_id')) {
                $table->string('provider_cycle_id')->nullable()->after('provider');
            }
        });

        if (Schema::hasColumn('subscriptions_cycles', 'pagarme_cycle_id')) {
            DB::statement("UPDATE subscriptions_cycles SET provider_cycle_id = pagarme_cycle_id WHERE provider_cycle_id IS NULL AND pagarme_cycle_id IS NOT NULL");
            DB::statement("UPDATE subscriptions_cycles SET provider = 'pagarme' WHERE provider IS NULL AND pagarme_cycle_id IS NOT NULL");
        }
    }
};
