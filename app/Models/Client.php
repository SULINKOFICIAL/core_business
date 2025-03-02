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
        'current_value',
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
