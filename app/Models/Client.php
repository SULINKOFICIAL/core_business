<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;

class Client extends Model
{
    protected $table = 'clients';

    protected $casts = [
        'first_user' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_installation',
        'install',
        'name',
        'email',
        'pagarme_customer_id',
        'whatsapp',
        'company',
        'cnpj',
        'cpf',
        'package_id',
        'users_limit',
        'logo',
        'table',
        'table_user',
        'table_password',
        'first_user',
        'token',
        'db_last_version',
        'db_error',
        'git_last_version',
        'git_error',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    // Pacote atual do cliente
    public function packages(): HasMany
    {
        return $this->hasMany(ClientPackage::class, 'client_id', 'id');
    }

    // Pacote atual do cliente
    public function package(): HasOne
    {
        return $this->hasOne(ClientPackage::class, 'client_id', 'id')->where('status', true);
    }

    // Domínios do cliente
    public function domains(): HasMany
    {
        return $this->hasMany(ClientDomain::class, 'client_id', 'id');
    }

    // Módulos habilitados para o cliente
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'clients_modules', 'client_id', 'module_id');
    }

    // Compras realizadas pelo cliente
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id', 'id');
    }

    // Compras realizadas pelo cliente
    public function cards(): HasMany
    {
        return $this->hasMany(ClientCard::class, 'client_id', 'id');
    }

    // Assinaturas realizadas pelo cliente
    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderSubscription::class,
            Order::class,
            'client_id',
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
