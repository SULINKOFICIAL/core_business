<?php

namespace App\Http\Controllers;

use App\Models\Tenant;

class TenantInstallController extends Controller
{
    /**
     * Retorna página de instalação do cliente.
     */
    public function index($id){

        // Obtém o cliente
        $tenant = Tenant::with('provisioning')->find($id);

        // Retorna a página
        return view('pages.tenants.install')->with([
            'client' => $tenant,
            'provisioning' => $tenant->provisioning,
        ]);

    }
}
