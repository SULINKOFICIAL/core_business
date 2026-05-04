<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\Request;

class TenantDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retorna a página. Os dados são carregados por AJAX (DataTables server-side).
        return view('pages.tenants_domains.index')->with([
            'tenants' => Tenant::query()->where('status', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valida dados da requisição.
        $data = $request->validate([
            'tenant_id'    => 'required|exists:tenants,id',
            'domain'       => 'required|string|max:255|unique:tenants_domains,domain',
            'description'  => 'nullable|string|max:255',
            'status'       => 'nullable|boolean',
        ]);

        // Normaliza o domínio removendo protocolo, barra final e caminho.
        $normalizedDomain = strtolower(trim($data['domain']));
        $normalizedDomain = preg_replace('#^https?://#', '', $normalizedDomain);
        $normalizedDomain = explode('/', $normalizedDomain)[0];
        $normalizedDomain = rtrim($normalizedDomain, '/');

        TenantDomain::create([
            'tenant_id'      => (int) $data['tenant_id'],
            'domain'         => $normalizedDomain,
            'description'    => $data['description'] ?? 'Domínio adicionado manualmente na central',
            'status'         => isset($data['status']) ? (bool) $data['status'] : true,
            'auto_generate'  => false,
        ]);

        return redirect()
            ->route('tenants.domains.index')
            ->with('message', 'Domínio adicionado com sucesso.');
    }
}
