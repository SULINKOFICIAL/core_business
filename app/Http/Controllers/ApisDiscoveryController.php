<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApisDiscoveryController extends Controller
{
    /**
     * Localiza tenant por email, CNPJ ou CPF e retorna domínio ativo.
     * Mantém contrato de descoberta usado por integrações externas.
     */
    public function findTenant(Request $request): JsonResponse
    {
        $data = $request->all();

        // Busca por identidade para manter compatibilidade com integrações antigas.
        $tenant = $this->findTenantByIdentity($data);

        if (!$tenant) {
            return response()->json(['message' => 'Não foi possível encontrar um cliente relacionado.'], 404);
        }

        $domain = $tenant->domains()->where('status', true)->first()?->domain;
        if (!$domain) {
            return response()->json(['message' => 'Tenante encontrado, mas sem domínio ativo vinculado.'], 404);
        }

        return response()->json(['domain' => $domain]);
    }

    /**
     * Resolve credenciais de banco pelo domínio solicitado no endpoint.
     * Trata domínio órfão para evitar retorno técnico inconsistente.
     */
    public function getDatabase(Request $request): JsonResponse
    {
        $domain = $request->query('domain');
        if (!$domain) {
            return response()->json(['error' => 'Domínio não fornecido.'], 400);
        }

        // Normalização simples para reduzir falso-negativo entre host com e sem www.
        $domain = str_replace('www.', '', $domain);
        $domain = TenantDomain::where('domain', $domain)->first();
        if (!$domain) {
            return response()->json(['error' => 'Domínio não encontrado.'], 404);
        }

        $tenant = $domain->tenant;
        if (!$tenant) {
            Log::warning('Domínio sem cliente vinculado na API getDatabase', [
                'domain_id' => $domain->id,
                'tenant_id' => $domain->tenant_id,
                'domain' => $domain->domain,
            ]);

            return response()->json(['error' => 'Domínio sem cliente vinculado.'], 404);
        }

        return response()->json([
            'tenant' => $tenant->id,
            'db_name' => $tenant->provisioning?->table,
            'db_user' => $tenant->provisioning?->table_user,
            'db_password' => $tenant->provisioning?->table_password,
        ]);
    }

    /**
     * Localiza tenant por identidade recebida em payload simples.
     * Preserva prioridade de busca usada no fluxo legado da API.
     */
    private function findTenantByIdentity(array $data): ?Tenant
    {
        if (!empty($data['email'])) {
            $tenant = Tenant::where('email', $data['email'])->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if (!empty($data['cnpj'])) {
            $tenant = Tenant::where('cnpj', $data['cnpj'])->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if (!empty($data['cpf'])) {
            return Tenant::where('cpf', $data['cpf'])->first();
        }

        return null;
    }
}
