<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company_profile')->nullable()->after('company');

            $table->string('company_zip_code', 8)->nullable()->after('company_profile');
            $table->string('company_city_state')->nullable()->after('company_zip_code');
            $table->string('company_address')->nullable()->after('company_city_state');
            $table->string('company_neighborhood')->nullable()->after('company_address');
            $table->string('company_number')->nullable()->after('company_neighborhood');
            $table->string('company_complement')->nullable()->after('company_number');

            $table->boolean('tips_whatsapp')->default(false)->after('company_complement');
            $table->boolean('tips_email')->default(false)->after('tips_whatsapp');

            $table->boolean('has_coupon')->default(false)->after('tips_email');
            $table->string('coupon_code')->nullable()->after('has_coupon');

            $table->enum('document_type', ['cnpj', 'cpf'])->default('cnpj')->after('coupon_code');
        });

        Schema::create('clients_main_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('goal');
            $table->timestamps();

            $table->unique(['client_id', 'goal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_main_goals');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_profile',
                'company_zip_code',
                'company_city_state',
                'company_address',
                'company_neighborhood',
                'company_number',
                'company_complement',
                'tips_whatsapp',
                'tips_email',
                'has_coupon',
                'coupon_code',
                'document_type',
            ]);
        });
    }
};
