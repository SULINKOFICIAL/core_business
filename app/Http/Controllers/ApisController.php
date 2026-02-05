<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ClientDomain;
use App\Models\ErrorMiCore;
use App\Models\IntegrationSuggestion;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Package;
use App\Models\PackageModule;
use App\Models\Ticket;
use App\Services\ERedeService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
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
    protected $eRedeService;

    public function __construct(Request $request, Client $content, ERedeService $eRedeService)
    {

        $this->request = $request;
        $this->repository = $content;
        $this->cpanelMiCore = new CpanelController();
        $this->eRedeService = $eRedeService;

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

    public function orders(Request $request) {

        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $orders = $client->orders()->orderBy('created_at', 'DESC')->get();

        // Inicia Json
        $ordersJson = [];

        // Formata dados Json
        foreach ($orders as $order) {

            // Date formated
            $buy['id']          = $order->id;
            $buy['date_created'] = $order->created_at;
            $buy['date_paid']   = $order->paid_at;
            $buy['type']        = $order->type;
            $buy['amount']      = $order->total();
            $buy['method']      = $order->method;
            $buy['description'] = $order->description;
            $buy['status']      = $order->status;
            $buy['packageName'] = $order->package->name;
            $buy['transactions'] = $order->transactions->count();
            
            // Se for a atribuição de um pacote
            if($buy['type'] == 'Pacote Trocado'){
                $buy['previousPackageName'] = $order->previousPackage->name;
            }

            // Obtém dados
            $ordersJson[] = $buy;

        }

        // Se o cliente tiver plano
        return response()->json($ordersJson, 200);

    }

    public function order(Request $request, $id) {

        // Recebe dados
        $data = $request->all();
        
         // Obtém dados do cliente
        $client = $data['client'];

        // Busca o pedido do cliente
        $order = Order::where('client_id', $client->id)->where('id', $id)->first();

        // Formata o pedido
        $orderJson['id']          = $order->id;
        $orderJson['date_created'] = $order->created_at;
        $orderJson['date_paid']   = $order->paid_at;
        $orderJson['type']        = $order->type;
        $orderJson['amount']      = $order->total();
        $orderJson['method']      = $order->method;
        $orderJson['description'] = $order->description;
        $orderJson['status']      = $order->status;
        $orderJson['packageName'] = $order->package->name;

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Pedido não encontrado', 404);

        // Obtém transações do pedido
        $transactions = $order->transactions;

        // Insere o pedido formatado
        $transactionsJson = [];

        // Formata dados Json
        foreach ($transactions as $transaction) {

            // Date formated
            $buy['id']           = $transaction->id;
            $buy['amount']       = $transaction->amount;
            $buy['method']       = $transaction->method;
            $buy['gateway']      = $transaction->gateway ? $transaction->gateway->name : null;
            $buy['date_created'] = $transaction->created_at;
            $buy['status']       = $transaction->status;

            // Obtém dados
            $transactionsJson[] = $buy;

        }

        // Insere as transações no pedido
        $orderJson['transactions'] = $transactionsJson;

        // Se o cliente tiver plano
        return response()->json($orderJson, 200);

    }


    public function cards(Request $request) {

        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $cards = $client->cards()->orderBy('created_at', 'DESC')->get();

        // Inicia Json
        $cardsJson = [];

        // Formata dados Json
        foreach ($cards as $card) {

            // Date formated
            $cardData['id']         = $card->id;
            $cardData['main']       = $card->main;
            $cardData['name']       = $card->name;
            $cardData['number']     = '**** **** **** ' . substr($card->number, -4);
            $cardData['expiration'] = str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) . '/' . $card->expiration_year;

            // Obtém dados
            $cardsJson[] = $cardData;

        }

        // Se o cliente tiver plano
        return response()->json($cardsJson, 200);

    }

    public function packages(Request $request)
    {
        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = $data['client'];
        
        // Obtém Pacotes do micore junto com os modulos
        $packages = Package::with('modules')->orderBy('order', 'ASC')->where('show_website', true)->where('status', true)->get();

        // Inicia Json
        $packageAvailable = [];

        // Formata dados Json
        foreach ($packages as $package) {

            // Date formated
            $packageData['id']                 = $package->id;
            $packageData['name']               = $package->name;
            $packageData['description']        = $package->description;
            $packageData['free']               = $package->free;
            $packageData['value']              = $package->value;
            $packageData['size_storage']       = $package->size_storage;
            $packageData['duration_days']      = $package->duration_days;
            $packageData['modules_ids']        = $package->modules()->pluck('module_id')->toArray();

            // Obtém dados
            $packageAvailable[] = $packageData;

        }

        // Retorna formatado em json
        return response()->json([
            'packages' => $packageAvailable,
            'actual'   => [
                'id'         => $client->package?->id,
                'name'       => $client->package?->name,
            ]
        ], 200);
    }

    /**
     * Retorna todos os módulos cadastrados
     * na central e também os pacotes que ele pertence.
     */
    public function modules()
    {

        // Obtém todos os módulos do sistema
        $modules = Module::with(['category'])->where('status', true)->get();

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
            $moduleData['packages']           = $module->packages()->pluck('package_id')->toArray();

            // Formata os preços
            $moduleData['pricing']['type']   = $module->pricing_type;

            // Se for cobrança unica
            if($module->pricing_type == 'usage'){
                $moduleData['pricing']['values'] = $module->pricing_type;
            } else {
                $moduleData['pricing']['value'] = $module->pricing_type;
            }
           
            // Obtém dados
            $moduleJson[] = $moduleData;

        }

        // Retorna formatado em json
        return response()->json($moduleJson, 200);

    }

    /**
     * Função responsável por processar pagamentos dos sistemas miCores.
     * Utilizamos junto a ele a integração através da eRede.
     */
    public function payment(Request $request, OrderService $service) {

        // Obtém os dados enviados no formulário
        $data = $request->all();

        // Caso seja um novo cartão
        if($data['card_id'] == 0){

            // Verifica se todos os dados para um cartão novo são validos
            if (
                !isset($data['token_micore'])     || 
                !isset($data['package_id'])       || 
                !isset($data['card_name'])        || 
                !isset($data['card_number'])      || 
                !isset($data['expiration_month']) || 
                !isset($data['expiration_year'])
            ){
                return response()->json(['message' => 'Parâmetros faltando'], 400);
            }

        }

        // Obtém cliente associado ao miCore através do Token dele
        $client = Client::where('token', $data['token_micore'])->first();

        // Se não encontrar o cliente
        if(!$client) return response()->json(['message' => 'Cliente não encontrado'], 404);

        // Obtém o pacote que o cliente quer realizar o upgrade
        $package = Package::find($data['package_id']);

        // Encontra o cartão do cliente para reutilizar
        if($data['card_id'] != 0){

            // Encontra o cartão do cliente
            $card = ClientCard::where('client_id', $client->id)->where('id', $data['card_id'])->first();

            // Se não encontrar o cliente
            if(!$card) return response()->json(['message' => 'Cartão não encontrado para esse cliente'], 404);

        } else {

            // Limpa os dados do cartão
            $data['card_number'] = (int) str_replace(' ', '', $data['card_number']);
    
            // Busca o cartão do cliente
            $card = ClientCard::where('client_id', $client->id)->where('number', $data['card_number'])->first();
            
            // Salvamos o cartão do cliente
            $card = ClientCard::create([
                'client_id'        => $client->id,
                'main'             => true,
                'name'             => $data['card_name'],
                'number'           => $data['card_number'],
                'expiration_month' => $data['expiration_month'],
                'expiration_year'  => $data['expiration_year'],
            ]);

        }

        // Retorna o cliente atualizado
        $orderResponse = $service->createOrder($client, $package);

        // Se o cliente estiver tentando comprar o mesmo plano
        if($orderResponse['status'] == 'Falha'){
            // Retorna pacote atualizado
            return response()->json([
                'status' => 'Falha',
                'message' => $orderResponse['message'],
            ]);
        }

        // Extrai a intenção de pagamento
        $order = $orderResponse['order'];

        // Verifica se já não foi pago
        if($order->status == 'Pago'){
            return response()->json([
                'status' => 'Falha',
                'message' => 'Seu pedido já foi pago.',
            ]);
        }

        // Gera transação para processar o pedido
        $transaction = OrderTransaction::create([
            'order_id'   => $order->id,
            'amount'     => $order->total(),
            'method'     => 'Gateway',
            'gateway_id' => 1,
        ]);
        
        // Realiza transação do eRedeController aqui
        $responseRede = $this->eRedeService->transaction($transaction, $card, ($data['ccv'] ?? null));

        // Se foi pago atribui o pacote ao cliente
        if($responseRede['returnCode'] == '00'){

            /**
             * Aqui armazenamos a primeiro brandTid ao cartão
             * para próximas cobranças sejam atreladas a esse primeira.
             */
            if(!$card->brand_tid){
                $card->brand_tid = $responseRede['brandTid'];
                $card->brand_tid_at = now();
                $card->save();
            }

            // Salta o brandTid referente a transação em questão.
            $transaction->brand_tid = $responseRede['brandTid'];
            $transaction->brand_tid_at = now();
            $transaction->status = 'Pago';
            $transaction->response = json_encode($responseRede);
            $transaction->save();

            // Retorna o cliente atualizado
            $service->confirmPaymentOrder($order);

            // Retorna pacote atualizado
            return response()->json([
                'status'  => 'Sucesso',
                'package' => $client->package,
                'message' => 'Compra realizada com sucesso.',
            ]);

        } else {

            // Atualiza para pago
            $transaction->status = 'Falhou';
            $transaction->response = json_encode($responseRede);
            $transaction->save();

            /**
             * Traduz os erros da Rede.
             */
            $redeErrors = [

                // RETORNOS DO EMISSOR
                0 => 'Sucesso',
                101 => 'Não autorizado. Problemas no cartão, entre em contato com o emissor.',
                102 => 'Não autorizado. Verifique a situação da loja junto ao emissor.',
                103 => 'Não autorizado. Tente novamente.',
                104 => 'Não autorizado. Tente novamente.',
                105 => 'Não autorizado. Cartão restrito.',
                106 => 'Erro no processamento pelo emissor. Tente novamente.',
                107 => 'Não autorizado. Tente novamente.',
                108 => 'Não autorizado. Valor não permitido para este tipo de cartão.',
                109 => 'Não autorizado. Cartão inexistente.',
                110 => 'Não autorizado. Tipo de transação não permitido para este cartão.',
                111 => 'Não autorizado. Fundos insuficientes.',
                112 => 'Não autorizado. Data de validade expirada.',
                113 => 'Não autorizado. Risco moderado identificado pelo emissor.',
                114 => 'Não autorizado. O cartão não pertence à rede de pagamento.',
                115 => 'Não autorizado. Limite de transações permitidas no período excedido.',
                116 => 'Não autorizado. Entre em contato com o emissor do cartão.',
                117 => 'Transação não encontrada.',
                118 => 'Não autorizado. Cartão bloqueado.',
                119 => 'Não autorizado. Código de segurança inválido.',
                121 => 'Erro no processamento. Tente novamente.',
                122 => 'Transação previamente enviada.',
                123 => 'Não autorizado. Portador solicitou o término das recorrências junto ao emissor.',
                124 => 'Não autorizado. Entre em contato com a Rede.',
                170 => 'Transação de zero dólar não permitida para este cartão.',
                172 => 'CVC2 necessário para Transação de Zero Dólar ELO.',
                174 => 'Transação de zero dólar bem-sucedida.',
                175 => 'Transação de zero dólar negada.',

                // RETORNOS DE INTEGRAÇÃO
                1 => 'expirationYear: Tamanho de parâmetro inválido',
                2 => 'expirationYear: Formato de parâmetro inválido',
                3 => 'expirationYear: Parâmetro obrigatório ausente',
                4 => 'cavv: Tamanho de parâmetro inválido',
                5 => 'cavv: Formato de parâmetro inválido',
                6 => 'postalCode: Tamanho de parâmetro inválido',
                7 => 'postalCode: Formato de parâmetro inválido',
                8 => 'postalCode: Parâmetro obrigatório ausente',
                9 => 'complement: Tamanho de parâmetro inválido',
                10 => 'complement: Formato de parâmetro inválido',
                11 => 'departureTax: Formato de parâmetro inválido',
                12 => 'documentNumber: Tamanho de parâmetro inválido',
                13 => 'documentNumber: Formato de parâmetro inválido',
                14 => 'documentNumber: Parâmetro obrigatório ausente',
                15 => 'securityCode: Tamanho de parâmetro inválido',
                16 => 'securityCode: Formato de parâmetro inválido',
                17 => 'distributorAffiliation: Tamanho de parâmetro inválido',
                18 => 'distributorAffiliation: Formato de parâmetro inválido',
                19 => 'xid: Tamanho de parâmetro inválido',
                20 => 'eci: Formato de parâmetro inválido',
                21 => 'xid: Parâmetro obrigatório ausente para cartão Visa',
                22 => 'street: Parâmetro obrigatório ausente',
                23 => 'street: Formato de parâmetro inválido',
                24 => 'affiliation: Tamanho de parâmetro inválido',
                25 => 'affiliation: Formato de parâmetro inválido',
                26 => 'affiliation: Parâmetro obrigatório ausente',
                27 => 'Parâmetro cavv ou eci ausente',
                28 => 'code: Tamanho de parâmetro inválido',
                29 => 'code: Formato de parâmetro inválido',
                30 => 'code: Parâmetro obrigatório ausente',
                31 => 'softdescriptor: Tamanho de parâmetro inválido',
                32 => 'softdescriptor: Formato de parâmetro inválido',
                33 => 'Mês: Formato de parâmetro inválido',
                34 => 'code: Formato de parâmetro inválido',
                35 => 'Mês: Parâmetro obrigatório ausente',
                36 => 'cardNumber: Tamanho de parâmetro inválido',
                37 => 'cardNumber: Formato de parâmetro inválido',
                38 => 'cardNumber: Parâmetro obrigatório ausente',
                39 => 'reference: Tamanho de parâmetro inválido',
                40 => 'reference: Formato de parâmetro inválido',
                41 => 'reference: Parâmetro obrigatório ausente',
                42 => 'reference: Número de pedido já existe',
                43 => 'number: Tamanho de parâmetro inválido',
                44 => 'number: Formato de parâmetro inválido',
                45 => 'number: Parâmetro obrigatório ausente',
                46 => 'installments: Não corresponde à transação de autorização',
                47 => 'origin: Formato de parâmetro inválido',
                48 => 'brandTid: Tamanho de parâmetro inválido',
                49 => 'O valor da transação excede o autorizado',
                50 => 'installments: Formato de parâmetro inválido',
                51 => 'Produto ou serviço desativado para este comerciante. Contate a Rede',
                53 => 'Transação não permitida para o emissor. Contate a Rede',
                54 => 'installments: Parâmetro não permitido para esta transação',
                55 => 'cardHolderName: Tamanho de parâmetro inválido',
                56 => 'Erro nos dados informados. Tente novamente',
                57 => 'affiliation: Comerciante inválido',
                58 => 'Não autorizado. Contate o emissor',
                59 => 'cardHolderName: Formato de parâmetro inválido',
                60 => 'street: Tamanho de parâmetro inválido',
                61 => 'subscription: Formato de parâmetro inválido',
                63 => 'softdescriptor: Não habilitado para este comerciante',
                64 => 'Transação não processada. Tente novamente',
                65 => 'token: Token inválido',
                66 => 'departureTax: Tamanho de parâmetro inválido',
                67 => 'departureTax: Formato de parâmetro inválido',
                68 => 'departureTax: Parâmetro obrigatório ausente',
                69 => 'Transação não permitida para este produto ou serviço',
                70 => 'amount: Tamanho de parâmetro inválido',
                71 => 'amount: Formato de parâmetro inválido',
                72 => 'Contate o emissor',
                73 => 'amount: Parâmetro obrigatório ausente',
                74 => 'Falha na comunicação. Tente novamente',
                75 => 'departureTax: Parâmetro não deve ser enviado para este tipo de transação',
                76 => 'kind: Formato de parâmetro inválido',
                78 => 'Transação não existe',
                79 => 'Cartão expirado. Transação não pode ser reenviada. Contate o emissor',
                80 => 'Não autorizado. Contate o emissor (Fundos insuficientes)',
                82 => 'Transação não autorizada para cartão de débito',
                83 => 'Não autorizado. Contate o emissor',
                84 => 'Não autorizado. Transação não pode ser reenviada. Contate o emissor',
                85 => 'complement: Tamanho de parâmetro inválido',
                86 => 'Cartão expirado',
                87 => 'Pelo menos um dos campos a seguir deve ser preenchido: tid ou reference',
                88 => 'Comerciante não aprovado. Regularize seu site e contate a Rede para voltar a transacionar',
                89 => 'token: Token inválido',
                97 => 'tid: Tamanho de parâmetro inválido',
                98 => 'tid: Formato de parâmetro inválido',
                99 => 'BusinessApplicationIdentifier: Formato de parâmetro inválido',
                100 => 'WalletId: Formato de parâmetro inválido',
                132 => 'DirectoryServerTransactionId: Tamanho de parâmetro inválido',
                133 => 'ThreedIndicator: Valor de parâmetro inválido',
                150 => 'Tempo esgotado. Tente novamente',
                151 => 'installments: Maior do que o permitido',
                153 => 'documentNumber: Número inválido',
                154 => 'embedded: Formato de parâmetro inválido',
                155 => 'eci: Parâmetro obrigatório ausente',
                156 => 'eci: Tamanho de parâmetro inválido',
                157 => 'cavv: Parâmetro obrigatório ausente',
                158 => 'capture: Tipo não permitido para esta transação',
                159 => 'userAgent: Tamanho de parâmetro inválido',
                160 => 'urls: Parâmetro obrigatório ausente (kind)',
                161 => 'urls: Formato de parâmetro inválido',
                167 => 'JSON de solicitação inválido',
                169 => 'Tipo de Conteúdo inválido',
                171 => 'Operação não permitida para esta transação',
                173 => 'Autorização expirada',
                176 => 'urls: Parâmetro obrigatório ausente (url)',
                898 => 'PV com IP de origem inválido',
                899 => 'Sem sucesso. Contate a Rede',
                1002 => 'Wallet Id: Tamanho de parâmetro inválido',
                1003 => 'Wallet Id: Parâmetro obrigatório ausente',
                1018 => 'MCC Tamanho Inválido',
                1019 => 'Parâmetro MCC Requerido',
                1020 => 'MCC Formato Inválido',
                1021 => 'PaymentFacilitatorID Tamanho Inválido',
                1023 => 'PaymentFacilitatorID Formato Inválido',
                1027 => 'SubMerchant: SubMerchantID Tamanho Inválido',
                1030 => 'CitySubMerchant Tamanho Inválido',
                1032 => 'SubMerchant: Estate Tamanho Inválido',
                1034 => 'CountrySubMerchant Tamanho Inválido',
                1036 => 'CepSubMerchant Tamanho Inválido',
                1038 => 'CnpjSubMerchant Tamanho Inválido',
                3025 => 'Negar Categoria 01: Este cartão não deve ser usado',
                3026 => 'Negar Categoria 02: Este cartão não deve ser usado neste PV',
                3027 => 'Negar Categoria 03: Nenhum cartão deve ser usado neste PV',
                3028 => 'Wallet Processing Type: Parâmetro obrigatório ausente',
                3029 => 'Wallet Processing Type: Tamanho de parâmetro inválido',
                3030 => 'Wallet Processing Type: Formato de parâmetro inválido',
                3031 => 'Wallet Sender Tax Identification: Parâmetro obrigatório ausente',
                3032 => 'Wallet Sender Tax Identification: Tamanho de parâmetro inválido',
                3033 => 'Wallet Sender Tax Identification: Formato de parâmetro inválido',
                3034 => 'SubMerchant: Tax Identification Number Tamanho Inválido',
                3035 => 'SubMerchant: Tax Identification Number Formato Inválido',
                3052 => 'Wallet Code: Parâmetro obrigatório ausente',
                3053 => 'Wallet Code: Formato de parâmetro inválido',
                3054 => 'Wallet Code: Tamanho de parâmetro inválido',
                3055 => 'Wallet Code: Parâmetro não permitido',
                3056 => 'Wallet Id: Parâmetro não permitido',
                3064 => 'Sai: Tamanho de parâmetro inválido',
                3065 => 'Sai: Formato de parâmetro inválido',
                3066 => 'Sai: Parâmetro obrigatório ausente',
                3067 => 'Cryptogram: Parâmetro obrigatório ausente',
                3068 => 'Credential Id: Parâmetro obrigatório ausente',
                3069 => 'Credential Id: Formato de parâmetro inválido',
                3070 => 'Credential Id: Tamanho de parâmetro inválido',

                // RETORNOS 3DS
                200 => 'Titular do cartão autenticado com sucesso',
                201 => 'Autenticação não necessária',
                202 => 'Titular do cartão não autenticado',
                203 => 'Serviço de autenticação não registrado para o comerciante. Entre em contato com a Rede',
                204 => 'Titular do cartão não registrado no programa de autenticação do emissor',
                220 => 'Solicitação de transação com autenticação recebida. URL de redirecionamento enviada',
                250 => 'onFailure: Parâmetro obrigatório ausente',
                251 => 'onFailure: Formato de parâmetro inválido',
                252 => 'urls: Parâmetro obrigatório ausente (url/threeDSecureFailure)',
                253 => 'urls: Tamanho de parâmetro inválido (url/threeDSecureFailure)',
                254 => 'urls: Formato de parâmetro inválido (url/threeDSecureFailure)',
                255 => 'urls: Parâmetro obrigatório ausente (url/threeDSecureSuccess)',
                256 => 'urls: Tamanho de parâmetro inválido (url/threeDSecureSuccess)',
                257 => 'urls: Formato de parâmetro inválido (url/threeDSecureSuccess)',
                258 => 'userAgent: Parâmetro obrigatório ausente',
                259 => 'urls: Parâmetro obrigatório ausente',
                260 => 'urls: Parâmetro obrigatório ausente (kind/threeDSecureFailure)',
                261 => 'urls: Parâmetro obrigatório ausente (kind/threeDSecureSuccess)',

                // RETORNOS DE CANCELAMENTO
                351 => 'Proibido',
                353 => 'Transação não encontrada',
                354 => 'Transação com período expirado para reembolso',
                355 => 'Transação já cancelada',
                357 => 'Soma dos reembolsos maior que o valor da transação',
                358 => 'Soma dos reembolsos maior que o valor processado disponível para reembolso',
                359 => 'Reembolso bem-sucedido',
                360 => 'Solicitação de reembolso foi bem-sucedida',
                362 => 'RefundId não encontrado',
                363 => 'Caracteres de URL de retorno excederam 500',
                365 => 'Reembolso parcial não disponível',
                368 => 'Sem sucesso. Tente novamente',
                369 => 'Reembolso não encontrado',
                370 => 'Solicitação falhou. Contate a Rede',
                371 => 'Transação não disponível para reembolso. Tente novamente em algumas horas',
                373 => 'Nenhum reembolso adicional permitido',
                374 => 'Reembolso não permitido. Contestação solicitada',

            ];

            // Retorna pacote atualizado
            return response()->json([
                'status' => 'Falha',
                'message' => 'Ocorreu um problema ao realizar a compra: ' . $redeErrors[$responseRede['returnCode']],
            ]);
            
        }

    }

}
