<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantPackage;
use App\Models\TenantPackageItem;
use App\Models\TenantProvisioning;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    private $repository;

    public function __construct(Tenant $content)
    {
        $this->repository = $content;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retorna a página. Os dados são carregados por AJAX (DataTables server-side).
        return view('pages.tenants.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Retorna a página
        return view('pages.tenants.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = 1;

        // Gera um domínio permitido
        $data['domain'] = verifyIfAllow($data['domain']);

        // Gera um nome de tabela permitido
        $domainClean = str_replace('-', '_', $data['domain']);

        // Insere prefixo do miCore
        $data['table'] = env('CPANEL_PREFIX') . '_' . $domainClean;

        // Insere prefixo do miCore
        $data['table_user'] = env('CPANEL_PREFIX') . '_' . $domainClean;

        // Gera senha
        $data['table_password'] = Str::random(12);

        // Gera token para API
        $data['token'] = hash('sha256', $data['domain'] . microtime(true));

        // Gera usuário
        $data['first_user'] = [
            'name'       => $data['user']['name'],
            'email'      => $data['user']['email'],
            'password'   => $data['user']['password'],
            'short_name' => generateShortName($data['user']['name']),
        ];

        $provisioningData = [
            'table' => $data['table'],
            'table_user' => $data['table_user'],
            'table_password' => $data['table_password'],
            'first_user' => $data['first_user'],
            'install' => TenantProvisioning::STEP_SUBDOMAIN,
        ];

        unset($data['table'], $data['table_user'], $data['table_password'], $data['first_user'], $data['user']);

        // Insere no banco de dados
        $created = $this->repository->create($data);
        $created->provisioning()->create($provisioningData);
        $created->runtimeStatus()->create();

        // Cria o pacote do cliente
        $package = TenantPackage::create([
            'tenant_id' => $created->id,
            'name' => 'DEMO 30 DIAS',
            'price' => 0,
            'status' => 1,
            'created_at' => now(),
        ]);

        $modules = Module::where('module_category_id', 1)
            ->where('status', true)
            ->get();

        $packageItems = $modules->map(function($module) use ($package) {
            return [
                'package_id' => $package->id,
                'item_id' => $module->id,
                'module_name' => $module->name,
                'module_value' => $module->value,
                'billing_type' => $module->pricing_type,
                'payload' => $module->toJson(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        // Cria os itens do pacote
        TenantPackageItem::insert($packageItems);

        // Cria uma assinatura fictícia
        $subscription = Subscription::create([
            'pagarme_subscription_id' => '1',
            'pagarme_card_id' => '1',
            'interval' => 'year',
            'payment_method' => 'liberado',
            'currency' => 'BRL',
            'installments' => 1,
            'status' => 'paid',
            'created_at' => now(),
        ]);

        // Cria um pedido fictício
        $order = Order::create([
            'tenant_id' => $created->id,
            'package_id' => $package->id,
            'subscription_id' => $subscription->id,
            'total_amount' => 0,
            'status' => 'Liberado',
            'type' => 'MIGRAÇÃO',
            'current_step' => 'Pagamento',
            'created_at' => now(),
        ]);

        // Cria um ciclo de assinatura fictício
        SubscriptionCycle::create([
            'subscription_id' => $subscription->id,
            'pagarme_cycle_id' => '1',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'status' => 'billed',
            'cycle' => 1,
            'billing_at' => now(),
            'next_billing_at' => now()->addDays(30),
            'created_at' => now(),
        ]);

        // Cria uma transação fictícia
        OrderTransaction::create([
            'order_id' => $order->id,
            'subscription_id' => $subscription->id,
            'pagarme_transaction_id' => '1',
            'amount' => 0,
            'status' => 'paid',
            'method' => 'liberado',
            'currency' => 'BRL',
            'created_at' => now(),
        ]);

        // Registra o domínio do cliente
        TenantDomain::create([
            'tenant_id'     => $created->id,
            'auto_generate' => true,
            'domain'        => $data['domain'] . '.micore.com.br',
            'description'   => 'Domínio cadastrado ao criar a conta do cliente',
            'status'        => true,
        ]);

        // Retorna a página
        return redirect()
                ->route('tenants.install.index', $created->id)
                ->with('message', 'Tenante <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtém dados dos Modulos ativos
        $modules = Module::with(['category', 'resources'])
            ->where('status', true)
            ->get();

        $modulesByCategory = $modules->groupBy(function ($module) {
            return optional($module->category)->name ?: 'Sem Categoria';
        });

        // Obtém dados do Tenante
        $tenant = $this->repository->find($id);

        // Valida se não aconteceu algum erro com a API
        $apiError = false;

        // Inicia Array para receber as permissões liberadas do cliente
        $allowFeatures = [];

        // Inicia Array para receber os modulos liberados do cliente
        $allowModules = [];

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiVerifyStatus = $this->guzzle('get', 'sistema/status', $tenant);

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiGetPermissions = $this->guzzle('get', 'sistema/permissoes', $tenant);

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiGetModules = $this->guzzle('get', 'sistema/modulos', $tenant);

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiGetSubscription = $this->guzzle('get', 'sistema/assinatura', $tenant);

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiGetUsers = $this->guzzle('get', 'sistema/usuarios', $tenant);

        // Realiza consulta para verificar se consegue se comunicar com o miCore
        $apiGetStorage = $this->guzzle('get', 'sistema/armazenamento', $tenant);

        dd($apiVerifyStatus);

        // Se conseguir conectar ao miCore do cliente
        if(!isset($apiVerifyStatus['error'])){
    
            // Transforma em uma coleção
            $apiGetPermissions = $apiGetPermissions['permissions'];
            $apiGetModules     = $apiGetModules['modules'];
    
            // Separa variáveis
            foreach ($apiGetPermissions as $value) {
                $allowFeatures[$value['name']] = $value['status'];
            }
    
            // Separa variáveis
            foreach ($apiGetModules as $value) {
                $allowModules[$value['name']] = $value['status'];
            }

            $allowSubscription = $apiGetSubscription['subscription'];

            $totalUsers = $apiGetUsers['users'] ?? 0;

            $limitUsers = $apiGetUsers['limit'] ?? 0;

            $totalStorage = $apiGetStorage['used_storage'] ?? 0;

            $limitStorage = $apiGetStorage['allow_storage'] ?? 0;

            $totalStorageGB = round($totalStorage / (1024 * 1024 * 1024), 2);
            
            $limitStorageGB = round($limitStorage / (1024 * 1024 * 1024), 2);

        } else {
            $apiError = true;
        }

        // Obtém pacotes
        $packages = Package::where('status', true)->get();

        // Retorna a página
        return view('pages.tenants.show')->with([
            'client'            => $tenant,
            'modules'           => $modules,
            'modulesByCategory' => $modulesByCategory,
            'packages'          => $packages,
            'allowFeatures'     => $allowFeatures,
            'allowModules'      => $allowModules,
            'allowSubscription' => $allowSubscription,
            'apiError'          => $apiError,
            'apiGetPermissions' => $apiGetPermissions,
            'totalUsers'        => $totalUsers,
            'limitUsers'        => $limitUsers,
            'totalStorage'      => $totalStorageGB,
            'limitStorage'      => $limitStorageGB,
        ]);

    }

    /**
     * Realiza uma solicitação Guzzle com autenticação Bearer
     *
     * @param string $method Método HTTP (get, post, etc)
     * @param string $url URL para a solicitação
     * @param object $tenant Objeto cliente contendo informações do cliente
     * @param array|null $data Dados opcionais para incluir na requisição
     * @return array Resposta da API
     */
    public function guzzle($method, $url, $tenant, $data = null)
    {
        try {
            // Instancia o Guzzle
            $guzzle = new Guzzle();

            // Inicializa os parâmetros da requisição
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.central.token'),
                ]
            ];

            // Se houver dados, adiciona ao corpo da requisição
            if ($data !== null) {
                $options['json'] = $data;
            }

            // Realiza a solicitação
            $response = $guzzle->$method("{$tenant->domains[0]->domain}/api/$url", $options);

            // Obtém o corpo da resposta
            $response = $response->getBody()->getContents();

            // Decodifica o JSON
            $response = json_decode($response, true);
            
            // Retorna a resposta
            return $response;

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Obtém dados
        $content = $this->repository->find($id);

        // Verifica se existe
        if(!$content) return redirect()->back();

        // Retorna a página
        return view('pages.tenants.edit')->with([
            'content' => $content
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Verifica se existe
        if(!$content = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $content->update($data);

        // Salva logo
        if(isset($data['fileLogo'])) $this->saveLogo($content, $data['fileLogo']);

        // Retorna a página
        return redirect()
                ->route('tenants.index')
                ->with('message', 'Tenante <b>'. $request->name . '</b> atualizado com sucesso.');

    }

    /**
     * Salva a logo do cliente, caso enviada.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $logo
     * @param  \App\Models\Tenant  $tenant
     * @param  string  $filename
     * @return void
     */
    public function saveLogo($tenant, $logo = null, $filename = 'logo.png')
    {
        if ($logo && $logo->isValid()) {
            $logo->storeAs("clientes/{$tenant->id}", $filename, 'public');
            $tenant->logo = true;
            $tenant->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // Obtém dados
        $content = $this->repository->findOrFail($id);

        // Atualiza status
        if($content->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
            $status = false;
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
            $status = true;
        }

        if ($this->request->ajax()) {
            return response()->json([
                'message' => 'Tenante <b>'. $content->name . '</b> '. $message .' com sucesso.',
                'status' => $status
            ]);
        }

        // Retorna a página
        return redirect()
                ->route('tenants.index')
                ->with('message', 'Tenante <b>'. $content->name . '</b> '. $message .' com sucesso.');

    }

}
