<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'logo',
        'table',
        'password',
        'token',
        'status',
        'filed_by',
        'created_by',
        'updated_by',
    ];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'clients_packages')
                    ->withPivot('start_date', 'end_date', 'status')
                    ->withTimestamps();
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
