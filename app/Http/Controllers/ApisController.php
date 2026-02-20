<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientDomain;
use App\Models\ErrorMiCore;
use App\Models\IntegrationSuggestion;
use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Models\Ticket;
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

        // Cria mensagens de erro para email, CNPJ ou CPF
        $verifications = ['email' => 'Já existe uma conta com esse email'];

        // Limpa CNPJ e CPF
        if (!empty($data['cnpj'])){
            $verifications['cnpj'] = 'Já existe uma conta com esse CNPJ';
            $data['cnpj'] = onlyNumbers($data['cnpj']);
        } 
        if (!empty($data['cpf'])){
            $verifications['cpf'] = 'Já existe uma conta com esse CPF';
            $data['cpf']  = onlyNumbers($data['cpf']);
        }

        // Limpa WhatsApp
        $data['whatsapp'] = onlyNumbers($data['whatsapp']);

        // Realiza verificações de duplicidade
        foreach ($verifications as $field => $message) {
            if (!empty($data[$field])) {
                if ($client = Client::where($field, $data[$field])->first()) {
                    return response()->json([
                        'message' => $message,
                        'url'     => $client->domain,
                    ], 409);
                }
            }
        }

        // Gera um domínio permitido
        $data['domain'] = verifyIfAllow($data['company']);

        // Gera um nome de tabela permitido
        $data['table'] = $data['table_usr'] = str_replace('-', '_', $data['domain']);

        // Insere prefixo do miCore
        $data['table'] = env('CPANEL_PREFIX') . '_' . $data['table'];

        // Gera senha
        $data['table_password'] = Str::random(12);

        // Gera token para API
        $data['token'] = hash('sha256', $data['company'] . microtime(true));

        // Adiciona o sufixo dos domínios Core
        $data['domain'] = $data['domain'] . '.micore.com.br';
        
        // Gera usuário
        $data['first_user'] = [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
            'short_name' => generateShortName($data['name']),
        ];

        // Insere no banco de dados
        $client = $this->repository->create($data);

        // Simula solicitação de troca de pacote
        $request = new Request(['package_id' => 1]);

        // Adiciona pacote básico ao cliente
        app(PackageController::class)->assign($request, $client->id);

        // Gera subdomínio, banco de dados e usuário no Cpanel miCore.com.br
        return $this->cpanelMiCore->make($client);        

    }

    /**
     * Função responsável por obter o cliente no banco de dados
     * e pegar o dominio do cliente para acessar via micore.com.br
     */
    public function findClient(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = isset($data['email']) ? Client::where('email', $data['email'])->first()
                : (isset($data['cnpj']) ? Client::where('cnpj', $data['cnpj'])->first()
                : (isset($data['cpf']) ? Client::where('cpf', $data['cpf'])->first()
                : null));

        // Verifica se o cliente foi encontrado
        if ($client) {
            return response()->json(['domain' => $client->domains[0]->domain]);
        } else {
            return response()->json(['message' => 'Não foi possível encontrar um cliente relacionado.'], 404);
        }
    }


    /**
     * Função responsável por obter a database do sistema que esta acessando
     * o sistema, fazemos isso filtrando o domínio que fez a requisição.
     */
    public function getDatabase(Request $request){

        // Extrai o domínio
        $domain = $request->query('domain');

        // Verifica se existe um subdóminio
        if (!$domain) return response()->json(['error' => 'Domínio não fornecido.'], 400);

        // Remove o www. caso exista
        $domain = str_replace('www.', '', $domain);

        // Busca na lista de domínios
        $domain = ClientDomain::where('domain', $domain)->first();

        // Verifica se o domínio existe
        if (!$domain) return response()->json(['error' => 'Domínio não encontrado.'], 404);

        // Busca o banco de dados correspondente ao subdomínio
        $client = $domain->client;

        // Retorna os dados do banco de dados
        return response()->json([
            'tenant'        => $client->id,
            'db_name'       => $client->table,
            'db_user'       => $client->table_user,
            'db_password'   => $client->table_password,
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

    public function suggestions(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Registra a sugestão no banco de dados
        IntegrationSuggestion::create($data);

        // Retorna resposta
        return response()->json('Sugestão enviada com sucesso!', 201);

    }

    public function plan(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $package = $client->package;

        // Se o cliente não tiver pacote
        if (!$package) return response()->json([
            'package' => null,
            'renovation' => 0,
        ], 200);

        // Formata o pacote do cliente
        $package['modules'] = $package->modules;

        // Se o cliente tiver plano
        if($package){

            // Obtém pedido de renovação do cliente
            $existsRenovation = $client->orders()->where('type', 'Renovação')->where('status', 'pendente')->exists();

            return response()->json([
                'package'     => $package,
                'renovation'  => $client->renovation(),
                'existsOrder' => $existsRenovation, 
            ], 200);
            
        } else {
            return response()->json([
                'package' => null,
                'renovation' => 0,
            ], 200);
        }

    }


    /**
     * Retorna todos os módulos cadastrados
     * na central e também os pacotes que ele pertence.
     */
    public function modules()
    {

        // Obtém todos os módulos do sistema
        $modules = Module::with(['category', 'pricingTiers'])->where('status', true)->get();

        // Inicia Json
        $moduleJson = [];

        // Formata dados Json
        foreach ($modules as $module) {

            // Date formated
            $moduleData['id']                 = $module->id;
            $moduleData['name']               = $module->name;
            $moduleData['description']        = $module->description;
            $moduleData['category']           = $module->category?->name;
            $moduleData['cover_image']        = $module->cover_image ? asset('storage/modules/' . $module->id . '/' . $module->cover_image) : asset('assets/media/images/default.png');

            // Formata os preços
            $moduleData['pricing']['type'] = $module->pricing_type;

            // Se for cobrança por uso, retorna as faixas (tiers)
            if($module->pricing_type === 'Preço Por Uso'){

                $pricingTiers = $module->pricingTiers->sortBy('usage_limit')
                                                        ->values()
                                                        ->map(function ($tier) {
                                                            return [
                                                                'usage_limit' => $tier->usage_limit,
                                                                'price' => (float) $tier->price,
                                                            ];
                                                        })
                                                        ->toArray();


                $moduleData['pricing']['values'] = $pricingTiers;
            } else {
                $moduleData['pricing']['values'] = (float) $module->value;
            }
           
            // Obtém dados
            $moduleJson[] = $moduleData;

        }

        // Retorna formatado em json
        return response()->json($moduleJson, 200);

    }

}
