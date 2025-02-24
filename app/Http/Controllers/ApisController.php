<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ErrorMiCore;
use App\Models\Ticket;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Str;

class ApisController extends Controller
{
    
    /**
     * Controlador responsável por gerencia o atendimento SAC do Core.
     *
     * Este controlador se comunica com a API oficial do WhatsApp através
     * do Controller WhatsappApiController() para realizar operações como
     * envio de mensagens e templates.
     *
     * O controlador também utiliza um canal para comunicação em tempo
     * real das mensagens.
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

    public function newClient(Request $request){
        
        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = 0;

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

        // Gera nome curto
        $data['user']['short_name'] = generateShortName($data['user']['name']);

        // Adiciona o sufixo dos domínios Core
        $data['domain'] = $data['domain'] . '.micore.com.br';

        // Insere no banco de dados
        $this->repository->create($data);

        // Gera dado do banco de dados
        $database = [
            'name' => $data['table'],
            'password' => $data['password']
        ];

        // Gera subdomínio, banco de dados e usuário no Cpanel miCore.com.br
        $this->cpanelMiCore->make($data['domain'], $database, $data['user']);

        // Retorna a página
        return response([
            'url' => "https://" . $data['domain'],
            'message' => 'Conta criada com sucesso',
        ]);

    }





    public function getDatabase(Request $request){
        $subdomain = $request->query('subdomain');
        $token = $request->header('Authorization');

        // Verifica se o token começa com "Bearer "
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7); // Remove o "Bearer " do token
        }

        // Valida o token enviado pelo MiCore
        if (!$token || $token !== env('CENTRAL_CORE_TOKEN')) return response()->json(['error' => 'Token inválido.'], 401);

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

        // Verificar se a requisição é segura
        if(!isset($data['token'])) {
            return response()->json('Token não encontrado', 409);
        }  

        // Verificar se a requisição é segura
        if ($data['token'] !== env('CENTRAL_CORE_TOKEN')){
            return response()->json('Token não autorizado', 409);
        }

        // Registra erro que veio através do MiCore
        ErrorMiCore::create($data);

        // Retornar resposta
        return response()->json('Registrou o erro', 201);

    }

    public function tickets(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Verificar se a requisição é segura
        if(!isset($data['token'])) {
            return response()->json('Token não encontrado', 409);
        }  

        // Verificar se a requisição é segura
        if ($data['token'] !== env('CENTRAL_CORE_TOKEN')){
            return response()->json('Token não autorizado', 409);
        }
        
        // Registra o ticket no banco de dados
        Ticket::create($data);

        // Retorna resposta
        return response()->json('Ticket criado com sucesso!', 201);

    }

}
