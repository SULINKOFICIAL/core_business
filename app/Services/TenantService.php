<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class TenantService
{
    /**
     * Função responsavel por retornar o Tenant
     */
    public function findTenant(string $customerId, string $provider = 'id'): Tenant
    {
        // Obtem a coluna pelo tipo de provedor
        $column = match ($provider) {
            'pagarme' => 'pagarme_customer_id',
            default   => 'id',
        };

        // Busca o tenant pela coluna e pelo customerId
        $tenant = Tenant::where($column, $customerId)->first();

        // Se não encontrar retorna erro
        if(!$tenant){
            throw new \Exception("Tenant não encontrado: {$column} - {$customerId}");
        }

        // Retorna o tenant
        return $tenant;
    }


    public function systemStatus(Tenant $tenant): string
    {
        if (!$tenant->token) {
            return 'Token Empty';
        }

        try {
            $domain = $tenant->domains()->orderBy('id')->value('domain');
            if (!$domain) {
                return 'Error';
            }

            $response = Http::withToken($tenant->token)->get("https://{$domain}/api/sistema/status");

            if ($response->successful() && ($response->json()['status'] ?? null) === 'ok') {
                return 'OK';
            }

            return 'Error';
        } catch (\Throwable $e) {
            return 'Error';
        }
    }
}

