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
        $client = Tenant::with('provisioning')->find($id);

        // Retorna a página
        return view('pages.clients.install')->with([
            'client' => $client,
            'provisioning' => $client->provisioning,
        ]);

    }
}
