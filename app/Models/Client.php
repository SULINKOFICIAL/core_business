<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;

class Client extends Model
{
    protected $table = 'clients';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'package_id',
        'users_limit',
        'logo',
        'table',
        'password',
        'token',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];
    
    // Pacote atual do cliente
    public function package(): HasOne
    {
       return $this->hasOne(Package::class, 'id', 'package_id');
    }
    
    // Módulos habilitados para o cliente
    public function modules(): BelongsToMany
    {
       return $this->belongsToMany(Module::class, 'clients_modules', 'client_id', 'module_id');
    }
    
    // Compras realizadas pelo cliente
    public function purchases(): HasMany
    {
       return $this->hasMany(ClientPurchase::class, 'client_id', 'id');
    }

    // Compras realizadas pelo cliente
    public function cards(): HasMany
    {
       return $this->hasMany(ClientCard::class, 'client_id', 'id');
    }
    
    // Assinaturas realizadas pelo cliente
    public function subscriptions(): HasMany
    {
       return $this->hasMany(ClientSubscription::class, 'client_id', 'id');
    }

    // Retorna em quantos dias deve ser feita a próxima renovação
    public function renovation()
    {
        $latestSubscription = $this->subscriptions()->latest('end_date')->first();
        if (!$latestSubscription || !$latestSubscription->end_date) {
            return null; // Sem assinatura ativa
        }
        return ceil(now()->diffInDays($latestSubscription->end_date));
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
