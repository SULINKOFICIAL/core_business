<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ErrorMiCore;
use App\Models\Package;
use App\Models\Ticket;
use App\Services\PackageService;
use Illuminate\Support\Str;

class ApisController extends Controller
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
     * Função responsável por criar clientes através de sites externos,
     * por exemplo o site comercial micore.com.br, mas também pode ser
     * utilizado para criar sistemas através de landing pages.
     */
    public function newClient(Request $request){

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = 1;

        // Gera um domínio permitido
        $data['domain'] = verifyIfAllow($data['name']);

        // Gera um nome de tabela permitido
        $data['table'] = str_replace('-', '_', $data['domain']);

        // Insere prefixo do miCore
        $data['table'] = 'micorecom_' . $data['table'];
        
        // Gera senha
        $data['password'] = Str::random(12);

        // Gera token para API
        $data['token'] = hash('sha256', $data['name'] . microtime(true));

        // Adiciona o sufixo dos domínios Core
        $data['domain'] = $data['domain'] . '.micore.com.br';

        // Associa pacote teste gratuito
        $data['package_id'] = 1;

        // Insere no banco de dados
        $client = $this->repository->create($data);

        // Adiciona pacote básico ao cliente
        app(PackageController::class)->assign($client->id, 1);

        // Gera dado do banco de dados
        $database = [
            'name' => $data['table'],
            'password' => $data['password']
        ];

        // Gera usuário
        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $request->password,
            'short_name' => generateShortName($data['name']),
        ];

        // Gera subdomínio, banco de dados e usuário no Cpanel miCore.com.br
        return $this->cpanelMiCore->make($data['domain'], $database, $user);        

    }

    /**
     * Função responsável por obter a database do sistema que esta acessando
     * o sistema, fazemos isso filtrando o domínio que fez a requisição.
     */
    public function getDatabase(Request $request){

        // Extrai o domínio
        $subdomain = $request->query('subdomain');

        // Verifica se existe um subdóminio
        if (!$subdomain) return response()->json(['error' => 'Subdomínio não fornecido.'], 400);

        // Busca o banco de dados correspondente ao subdomínio
        $client = Client::where('domain', $subdomain)->first();

        if (!$client) return response()->json(['error' => 'Empresa não encontrada.'], 404);

        return response()->json([
            'database_name' => $client->table,
            'db_user' => $client->table . '_usr',
            'db_password' => $client->password,
        ]);

    }

    public function notifyErrors(Request $request){
        
        // Recebe dados
        $data = $request->all();

        // Registra erro que veio através do MiCore
        ErrorMiCore::create($data);

        // Retornar resposta
        return response()->json('Registrou o erro', 201);

    }

    public function tickets(Request $request) {

        // Recebe dados
        $data = $request->all();
        
        // Registra o ticket no banco de dados
        Ticket::create($data);

        // Retorna resposta
        return response()->json('Ticket criado com sucesso!', 201);

    }

    public function plan(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = Client::where('token', $data['token'])->first();

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Conta não encontrada', 404);

        // Obtém plano atual do cliente
        $package = $client->package;

        // Se o cliente tiver plano
        if($package){
            return response()->json([
                'package' => $package,
                'renovation' => $client->renovation(),
            ], 200);
        } else {
            return response()->json([
                'package' => 'Sem Plano',
                'renovation' => 0,
            ], 200);
        }

    }

    public function payment(Request $request, PackageService $service) {

        // Obtém dados
        $data = $request->all();
        
        // Obtém cliente
        $client = Client::where('token', $data['token'])->first();

        // Obtém pacote desejado
        $package = Package::find($data['package_id']);

        // Retorna erro caso não encontre cliente ou pacote
        if (!$client || !$package) return response()->json(['error' => 'Cliente ou pacote não encontrado.'], 404);

        // Retorna o cliente atualizado
        $response = $service->assignNewPackage($client, $package);

        // Retorna pacote atualizado
        return response()->json([
            'message' => $response,
        ]);

    }

}
