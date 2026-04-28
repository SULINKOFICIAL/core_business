<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;

class Tenant extends Model
{
    protected $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_installation',
        'name',
        'email',
        'pagarme_customer_id',
        'whatsapp',
        'company',
        'cnpj',
        'cpf',
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
        'onboarding_current_step',
        'onboarding_started_at',
        'onboarding_completed_at',
        'package_id',
        'users_limit',
        'logo',
        'token',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    // Dados de provisionamento técnico do cliente
    public function provisioning(): HasOne
    {
        return $this->hasOne(TenantProvisioning::class, 'tenant_id', 'id');
    }

    // Status técnico de atualização do cliente
    public function runtimeStatus(): HasOne
    {
        return $this->hasOne(TenantRuntimeStatus::class, 'tenant_id', 'id');
    }

    // Planos do cliente
    public function plans(): HasMany
    {
        return $this->hasMany(TenantPlan::class, 'tenant_id', 'id');
    }

    // Plano atual do cliente
    public function plan(): HasOne
    {
        return $this->hasOne(TenantPlan::class, 'tenant_id', 'id')->where('status', true);
    }

    // Domínios do cliente
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id', 'id');
    }

    // Objetivos principais selecionados no onboarding
    public function mainGoals(): HasMany
    {
        return $this->hasMany(TenantMainGoal::class, 'tenant_id', 'id');
    }

    // Módulos habilitados para o cliente
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'tenants_modules', 'tenant_id', 'module_id');
    }

    // Compras realizadas pelo cliente
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'tenant_id', 'id');
    }

    // Compras realizadas pelo cliente
    public function cards(): HasMany
    {
        return $this->hasMany(TenantCard::class, 'tenant_id', 'id');
    }

    // Assinaturas realizadas pelo cliente
    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Subscription::class,
            Order::class,
            'tenant_id',
            'order_id',
            'id',
            'id'
        );
    }

    // Retorna a última compra realizada pelo cliente
    public function lastOrder()
    {
        return $this->orders()->latest('created_at')->first();
    }

    // Retorna em quantos dias deve ser feita a próxima renovação
    public function renovation()
    {
        // Obtém assinatura do cliente
        $latestOrder = $this->lastOrder();

        // Caso não encontre
        if (!$latestOrder) {
            return null;
        }

        // Obtém data de expiração
        $now = Carbon::now();
        return round($now->diffInDays($latestOrder->end_date));
    }

    // Retorna em quantos dias deve ser feita a próxima renovação
    public function lastSubscription()
    {
        return $this->subscriptions()->latest('end_date')->first();
    }

    public function systemStatus()
    {

        return 'OK';

        // Verifica se possui Token
        if (!$this->token) {
            return 'Token Empty';
        }

        // Tenta
        try {
            // Tenta realiza a requisição
            $response = Http::withToken($this->token)->get("https://$this->domain/api/sistema/status");

            // Se for bem sucedido e o sistema estiver ativo
            if ($response->successful() && $response->json()['status'] === 'ok') {
                return 'OK';
            }

            // Não esta funcionando
            return 'Error';
        } catch (\Exception $e) {
            // Erro encontrado
            return 'Error';
        }
    }
}
