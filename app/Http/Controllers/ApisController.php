<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ErrorMiCore;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Package;
use App\Models\Ticket;
use App\Services\ERedeService;
use App\Services\OrderService;
use Illuminate\Support\Str;

class ApisController extends Controller
{

    // Gerencia Serviço eRede
    protected $eRedeService;
    
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

        dd($data);

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
        $client = Client::where('token', $data['token_micore'])->first();

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

    public function orders(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = Client::where('token', $data['token_micore'])->first();

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Conta não encontrada', 404);

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
        $client = Client::where('token', $data['token_micore'])->first();

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Conta não encontrada', 404);

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


    /**
     * Função responsável por processar pagamentos dos sistemas miCores.
     * Utilizamos junto a ele a integração através da eRede.
     */
    public function payment(Request $request, OrderService $service) {

        // Obtém os dados enviados no formulário
        $data = $request->all();
        
        // Se não encontrar o cliente
        if (!isset($data['token_micore'])     || 
            !isset($data['package_id'])       || 
            !isset($data['card_name'])        || 
            !isset($data['card_number'])      || 
            !isset($data['expiration_month']) || 
            !isset($data['expiration_year'])) {
            return response()->json(['error' => 'Parâmetros faltando'], 400);
        }

        // Obtém cliente associado ao miCore através do Token dele
        $client = Client::where('token', $data['token_micore'])->first();

        // Se não encontrar o cliente
        if(!$client) return response()->json(['error' => 'Cliente não encontrado'], 404);

        // Obtém o pacote que o cliente quer realizar o upgrade
        $package = Package::find($data['package_id']);

        // Encontra o cartão do cliente para reutilizar
        if(isset($data['card_id'])){

            // Encontra o cartão do cliente
            $card = ClientCard::where('client_id', $client->id)->where('number', $data['card_number'])->first();

            // Se não encontrar o cliente
            if(!$client) return response()->json(['error' => 'Cartão não encontrado para esse cliente'], 404);

        } else {

            // Limpa os dados do cartão
            $data['card_number'] = (int) str_replace(' ', '', $data['card_number']);
    
            // Busca o cartão do cliente
            $card = ClientCard::where('client_id', $client->id)->where('number', $data['card_number'])->first();
            
            // Verifica se o cartão já não está cadastrado
            if (!$card) {

                // Salvamos o cartão do cliente
                $card = ClientCard::create([
                    'client_id'        => $client->id,
                    'name'             => $data['card_name'],
                    'number'           => $data['card_number'],
                    'expiration_month' => $data['expiration_month'],
                    'expiration_year'  => $data['expiration_year'],
                ]);

            } else {

                // Atualiza os dados do cartão existente
                $card->update([
                    'name'             => $data['card_name'],
                    'expiration_month' => $data['expiration_month'],
                    'expiration_year'  => $data['expiration_year'],
                ]);
            }

        }

        // Retorna o cliente atualizado
        $orderResponse = $service->createOrder($client, $package);

        // Se o cliente estiver tentando comprar o mesmo plano
        if($orderResponse['status'] == 'Falha'){
            // Retorna pacote atualizado
            return response()->json([
                'code' => 'Falha',
                'message' => $orderResponse['message'],
            ]);
        }

        // Extrai a intenção de pagamento
        $order = $orderResponse['order'];

        // Verifica se já não foi pago
        if($order->status == 'Pago'){// Retorna pacote atualizado
            return response()->json([
                'status' => 'Falha',
                'error' => 'Seu pedido já foi pago.',
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
                'status' => 'Sucesso',
                'message' => 'Compra realizada com sucesso.',
            ]);

        } else {

            // Atualiza para pago
            $transaction->status = 'Falhou';
            $transaction->response = json_encode($responseRede);
            $transaction->save();

            // Retorna pacote atualizado
            return response()->json([
                'status' => 'Falha',
                'error' => 'Ocorreu um problema ao realizar a compra: ',
                'redeCode' => $responseRede['returnCode'],
            ]);
        }


    }

}