<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ErrorMiCore;
use App\Models\Package;
use App\Models\Ticket;
use App\Services\ERedeService;
use App\Services\PackageService;
use Illuminate\Support\Facades\Log;
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

    public function purchases(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = Client::where('token', $data['token_micore'])->first();

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Conta não encontrada', 404);

        // Obtém plano atual do cliente
        $purchases = $client->purchases()->orderBy('created_at', 'DESC')->get();

        // Inicia Json
        $purchasesJson = [];

        // Formata dados Json
        foreach ($purchases as $purchase) {

            // Date formated
            $buy['id']          = $purchase->id;
            $buy['date']        = $purchase->purchase_date;
            $buy['type']        = $purchase->type;
            $buy['amount']      = $purchase->total();
            $buy['method']      = $purchase->method;
            $buy['status']      = $purchase->status;
            $buy['packageName'] = $purchase->package->name;
            
            // Se for a atribuição de um pacote
            if($buy['type'] == 'Pacote Trocado'){
                $buy['previousPackageName'] = $purchase->previousPackage->name;
            }

            // Obtém dados
            $purchasesJson[] = $buy;

        }

        // Se o cliente tiver plano
        return response()->json($purchasesJson, 200);

    }


    /**
     * Função responsável por processar pagamentos dos sistemas miCores.
     * Utilizamos junto a ele a integração através da eRede.
     */
    public function payment(Request $request, PackageService $service) {

        // Obtém os dados enviados no formulário
        $data = $request->all();

        // Se não encontrar o cliente
        if (!isset($data['token_micore']) || !isset($data['package_id'])) {
            return response()->json(['error' => 'Parâmetros faltando'], 400);
        }

        // Obtém cliente associado ao miCore através do Token dele
        $client = Client::where('token', $data['token_micore'])->first();

        // Se não encontrar o cliente
        if(!$client) return response()->json(['error' => 'Cliente não encontrado'], 404);

        // Obtém o pacote que o cliente quer realizar o upgrade
        $package = Package::find($data['package_id']);

        // Limpa os dados do cartão
        $data['card_number'] = (int) str_replace(' ', '', $data['card_number']);

        // Busca o cartão do cliente
        $card = ClientCard::where('client_id', $client->id)->where('number', $data['card_number'])->first();

        // Encontra o cartão do cliente para reutilizar
        if(isset($data['card_id'])){

            // Encontra o cartão do cliente
            $card = ClientCard::where('client_id', $client->id)
                                ->where('number', $data['card_number'])
                                ->first();

            return 'Cartão não encontrado para esse cliente';

        } else {
            
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
        $responsePaymentIntent = $service->createPaymentIntent($client, $package, 'Gateway', 1);

        // Se o cliente estiver tentando comprar o mesmo plano
        if($responsePaymentIntent['status'] == 'Falha'){
            // Retorna pacote atualizado
            return response()->json([
                'code' => 'Falha',
                'message' => $responsePaymentIntent['message'],
            ]);
        }

        // Extrai a intenção de pagamento
        $paymentIntention = $responsePaymentIntent['purchase'];

        // Formata o valor inteiro em centavos conforme eRede solicita
        $amount = (int) ($paymentIntention->total() * 100);

        // Formata a referencia da transação
        $reference = 'PTI' . $paymentIntention->id;
        
        // Realiza transação do eRedeController aqui
        $responseRede = $this->eRedeService->transaction($amount, $reference, $card, $data['ccv'] ?? null);

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
            $paymentIntention->brand_tid = $responseRede['brandTid'];
            $paymentIntention->brand_tid_at = now();
            $paymentIntention->status = 'Pago';
            $paymentIntention->save();

            // Retorna o cliente atualizado
            $service->confirmPackageChange($paymentIntention);

            // Retorna pacote atualizado
            return response()->json([
                'status' => 'Sucesso',
                'message' => 'Compra realizada com sucesso.',
            ]);

        } else {

            // Atualiza para pago
            $paymentIntention->status = 'Falha';
            $paymentIntention->response = json_encode($responseRede);
            $paymentIntention->save();

            Log::info(json_encode($responseRede));

            // Retorna pacote atualizado
            return response()->json([
                'status' => 'Falha',
                'error' => 'Ocorreu um problema ao realizar a compra: ',
                'redeCode' => $responseRede['returnCode'],
            ]);
        }


    }

}