<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientInstallController extends Controller
{
    /**
     * Retorna página de instalação do cliente.
     */
    public function index($id){

        // Obtém o cliente
        $client = Client::with('provisioning')->find($id);

        // Retorna a página
        return view('pages.clients.install')->with([
            'client' => $client,
            'provisioning' => $client->provisioning,
        ]);

    }
}
