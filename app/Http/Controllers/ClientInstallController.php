<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientInstallController extends Controller
{

    /**
     * Controlador responsável por gerenciar as APIs do sistema.
     *
     * Este controlador gerencia a criação das contas dos clientes na
     * central, e também é responsável por gerenciar a criação das contas
     * no cPanel que esta em um EC2 em nossa aplicação na Amazon.
     * 
     */
    protected $request;
    private $repository;
    private $cpanelMiCore;

    public function __construct(Request $request, Client $content)
    {

        $this->request = $request;
        $this->repository = $content;
        $this->cpanelMiCore = new CpanelController();

    }

    /**
     * Retorna página de instalação do cliente.
     */
    public function index($id){

        // Obtém o cliente
        $client = Client::find($id);

        // Retorna a página
        return view('pages.clients.install')->with([
            'client' => $client,
        ]);

    }
}
