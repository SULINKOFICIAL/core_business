<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientCard;
use App\Models\ClientDomain;
use App\Models\ErrorMiCore;
use App\Models\IntegrationSuggestion;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Models\Order;
use App\Models\OrderItem;
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
    protected $orderService;

    public function __construct(Request $request, Client $content, ERedeService $eRedeService, OrderService $orderService)
    {

        $this->request = $request;
        $this->repository = $content;
        $this->cpanelMiCore = new CpanelController();
        $this->eRedeService = $eRedeService;
        $this->orderService = $orderService;

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

    /**
     * Retorna o pedido em rascunho mais recente do cliente (se existir).
     * Obs.: Geralmente é o pedido em andamento.
     */
    public function orderDraft(Request $request)
    {

        /**
         * Extrai os dados
         */
        $data = $request->all();

        /**
         * Etrai o cliente
         */
        $client = $data['client'];

        /**
         * Busca o pedido em andamento
         */
        $order = Order::where('client_id', $client->id)
                            ->where('status', 'draft')
                            ->orderBy('created_at', 'DESC')
                            ->with(['items.configurations'])
                            ->first();

        /**
         * Monta os itens com os dados relevantes para o front
         */
        $items = $order->items->map(function ($item) {
            return [
                'id'                => $item->id,                  // Identificador do item
                'type'              => $item->type,                // Tipo do item (module/package/etc)
                'name'              => $item->item_name,           // Nome do item
                'item_key'          => $item->item_key,            // Chave do item
                'billing_type'      => $item->item_billing_type,   // Tipo de cobrança do item
            ];
        });

        /**
         * Calcula subtotal e desconto aplicado
         */
        $subtotalAmount = (float) $order->items()->sum('amount');
        $discountAmount = (float) ($order->coupon_discount_amount ?? 0);

        /**
         * Responde com o rascunho e os itens formatados
         */
        return response()->json([
            'order_id'          => $order->id,           // Identificador do pedido
            'status'            => $order->status,       // Status do pedido (draft)
            'current_step'      => $order->current_step, // Etapa atual do pedido
            'amount'            => $subtotalAmount,      // Subtotal antes de descontos
            'discount_amount'   => $discountAmount,      // Desconto aplicado por cupom
            'total_amount'      => $order->total(),      // Total calculado do pedido
            'currency'          => $order->currency,     // Moeda do pedido
            'items'             => $items,               // Itens do pedido
        ], 200);
    }

    /**
     * Retorna as opções de uso (tiers) para módulos do pedido.
     */
    public function orderUsageOptions(Request $request)
    {

        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        // Busca o rascunho do cliente com itens e configurações
        $order = Order::where('client_id', $client->id)
                            ->where('status', 'draft')
                            ->with(['items.configurations'])
                            ->first();

        // Inicia lista de módulos que exigem seleção de uso
        $usageModules = [];

        /**
         * Percorre os itens do pedido e filtra apenas módulos com
         * cobrança por uso para montar as opções de faixa.
         */
        foreach ($order->items as $item) {

            // Ignora itens que não são módulo ou sem chave de referência
            if ($item->type !== 'Módulo') {
                continue;
            }

            // Busca módulo por uso relacionado ao item do pedido
            $module = Module::where('id', $item->item_key)
                ->where('pricing_type', 'Preço Por Uso')
                ->first();

            // Ignora módulos fixos ou inexistentes
            if (!$module) {
                continue;
            }

            // Carrega as faixas (tiers) do módulo ordenadas por limite
            $tiers = ModulePricingTier::where('module_id', $module->id)
                ->orderBy('usage_limit')
                ->get()
                ->map(function ($tier) {
                    return [
                        'usage_limit' => $tier->usage_limit, // Limite da faixa
                        'price' => (float) $tier->price,     // Preço da faixa
                    ];
                })
                ->toArray();

            // Obtém uso já escolhido para o item (configuração ou snapshot)
            $usageConfig = null;
            $configItem = $item->configurations->firstWhere('key', 'usage');
            if ($configItem) {
                $usageConfig = $configItem->value;
            } elseif (is_array($item->pricing_model_snapshot ?? null) && isset($item->pricing_model_snapshot['usage'])) {
                $usageConfig = $item->pricing_model_snapshot['usage'];
            }

            // Adiciona módulo formatado para consumo no front
            $usageModules[] = [
                'module_id' => $module->id,           // ID do módulo
                'module_name' => $module->name,       // Nome do módulo
                'tiers' => $tiers,                    // Faixas disponíveis
                'selected_usage' => $usageConfig,     // Uso selecionado (se houver)
            ];
        }

        // Retorna opções de uso dos módulos do pedido
        return response()->json([
            'order_id' => $order->id,         // Identificador do pedido
            'modules'  => $usageModules,       // Módulos com opções de uso
        ], 200);
    }

    /**
     * Retorna o resumo de checkout do pedido.
     */
    public function orderCheckout(Request $request)
    {
        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        // Valida order_id
        if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
            return response()->json(['message' => 'order_id inválido'], 400);
        }

        // Busca o rascunho do cliente
        $order = Order::where('id', (int) $data['order_id'])
            ->where('client_id', $client->id)
            ->where('status', 'draft')
            ->with(['items.configurations'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        $items = $order->items->map(function ($item) {
            $usageConfig = null;
            $configItem = $item->configurations->firstWhere('key', 'usage');
            if ($configItem) {
                $usageConfig = $configItem->value;
            } elseif (is_array($item->pricing_model_snapshot ?? null) && isset($item->pricing_model_snapshot['usage'])) {
                $usageConfig = $item->pricing_model_snapshot['usage'];
            }

            return [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_name' => $item->item_name_snapshot,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price_snapshot,
                'subtotal' => $item->amount,
                'usage' => $usageConfig,
            ];
        });

        $subtotalAmount = (float) $order->items()->sum('amount');
        $discountAmount = (float) ($order->coupon_discount_amount ?? 0);

        return response()->json([
            'order_id' => $order->id,
            'amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $order->total(),
            'currency' => $order->currency,
            'coupon' => $order->coupon_id ? [
                'code' => $order->coupon_code_snapshot,
                'type' => $order->coupon_type_snapshot,
                'value' => $order->coupon_value_snapshot,
                'trial_months' => $order->coupon_trial_months,
                'discount_amount' => $discountAmount,
            ] : null,
            'items' => $items,
        ], 200);
    }

    /**
     * Atualiza a etapa atual do pedido em rascunho.
     */
    public function orderStep(Request $request)
    {
        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        // Validações básicas
        if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
            return response()->json(['message' => 'order_id inválido'], 400);
        }
        if (!isset($data['step']) || !is_string($data['step'])) {
            return response()->json(['message' => 'step inválido'], 400);
        }

        // Busca o pedido em rascunho do cliente
        $order = Order::where('id', (int) $data['order_id'])
            ->where('client_id', $client->id)
            ->where('status', 'draft')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        // Atualiza a etapa atual
        $order->current_step = $data['step'];
        $order->save();

        return response()->json([
            'order_id' => $order->id,
            'current_step' => $order->current_step,
        ], 200);
    }

    /**
     * Aplica um cupom ao pedido em rascunho.
     */
    public function orderApplyCoupon(Request $request)
    {
        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
            return response()->json(['message' => 'order_id inválido'], 400);
        }
        if (!isset($data['code']) || !is_string($data['code'])) {
            return response()->json(['message' => 'code inválido'], 400);
        }

        $code = strtoupper(trim($data['code']));
        if ($code === '') {
            return response()->json(['message' => 'code inválido'], 400);
        }

        // Busca o pedido em rascunho do cliente
        $order = Order::where('id', (int) $data['order_id'])
            ->where('client_id', $client->id)
            ->where('status', 'draft')
            ->with(['items.configurations'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        // Busca cupom por código (case-insensitive)
        $coupon = Coupon::whereRaw('upper(code) = ?', [$code])->first();
        if (!$coupon || !$coupon->is_active) {
            return response()->json(['message' => 'Cupom inválido'], 400);
        }

        $now = now();
        if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
            return response()->json(['message' => 'Cupom ainda não está válido'], 400);
        }
        if ($coupon->ends_at && $coupon->ends_at->lt($now)) {
            return response()->json(['message' => 'Cupom expirado'], 400);
        }

        if (!is_null($coupon->max_redemptions) && $coupon->redeemed_count >= $coupon->max_redemptions) {
            return response()->json(['message' => 'Limite de uso do cupom atingido'], 400);
        }

        // Se já existe cupom diferente, remove a redenção antiga
        $existingRedemption = CouponRedemption::where('order_id', $order->id)->first();
        if ($existingRedemption && $existingRedemption->coupon_id !== $coupon->id) {
            $previousCoupon = Coupon::find($existingRedemption->coupon_id);
            if ($previousCoupon && $previousCoupon->redeemed_count > 0) {
                $previousCoupon->decrement('redeemed_count');
            }
            $existingRedemption->delete();
            $existingRedemption = null;
        }

        // Calcula subtotal e desconto do cupom
        $subtotalAmount = (float) $order->items()->sum('amount');
        $discountAmount = $this->calculateCouponDiscountAmount($coupon, $subtotalAmount);

        // Atualiza o pedido com o cupom aplicado
        $order->update([
            'coupon_id' => $coupon->id,
            'coupon_code_snapshot' => $coupon->code,
            'coupon_type_snapshot' => $coupon->type,
            'coupon_value_snapshot' => $coupon->amount,
            'coupon_trial_months' => $coupon->trial_months,
            'coupon_applied_at' => $now,
            'coupon_discount_amount' => $discountAmount,
            'total_amount' => max(0, $subtotalAmount - $discountAmount),
        ]);

        // Registra redenção do cupom
        if (!$existingRedemption) {
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
                'client_id' => $client->id,
                'redeemed_at' => $now,
                'amount_discounted' => $discountAmount,
                'currency' => $order->currency,
                'code_snapshot' => $coupon->code,
                'type_snapshot' => $coupon->type,
                'value_snapshot' => $coupon->amount,
                'trial_months_snapshot' => $coupon->trial_months,
            ]);
            $coupon->increment('redeemed_count');
        } else {
            $existingRedemption->update([
                'amount_discounted' => $discountAmount,
                'currency' => $order->currency,
                'code_snapshot' => $coupon->code,
                'type_snapshot' => $coupon->type,
                'value_snapshot' => $coupon->amount,
                'trial_months_snapshot' => $coupon->trial_months,
            ]);
        }

        return response()->json($this->buildOrderSummary($order), 200);
    }

    /**
     * Remove o cupom aplicado no pedido em rascunho.
     */
    public function orderRemoveCoupon(Request $request)
    {
        // Extrai dados e cliente já anexado pelo middleware
        $data = $request->all();
        $client = $data['client'];

        if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
            return response()->json(['message' => 'order_id inválido'], 400);
        }

        // Busca o pedido em rascunho do cliente
        $order = Order::where('id', (int) $data['order_id'])
            ->where('client_id', $client->id)
            ->where('status', 'draft')
            ->with(['items.configurations'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        // Remove redenção do cupom, se existir
        $existingRedemption = CouponRedemption::where('order_id', $order->id)->first();
        if ($existingRedemption) {
            $coupon = Coupon::find($existingRedemption->coupon_id);
            if ($coupon && $coupon->redeemed_count > 0) {
                $coupon->decrement('redeemed_count');
            }
            $existingRedemption->delete();
        }

        $subtotalAmount = (float) $order->items()->sum('amount');

        // Limpa campos de cupom no pedido
        $order->update([
            'coupon_id' => null,
            'coupon_code_snapshot' => null,
            'coupon_type_snapshot' => null,
            'coupon_value_snapshot' => null,
            'coupon_trial_months' => null,
            'coupon_applied_at' => null,
            'coupon_discount_amount' => 0,
            'total_amount' => $subtotalAmount,
        ]);

        return response()->json($this->buildOrderSummary($order), 200);
    }

    /**
     * Calcula o desconto do cupom para um subtotal.
     */
    private function calculateCouponDiscountAmount(Coupon $coupon, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = 0.0;
        if ($coupon->type === 'percent') {
            $discount = $subtotal * ((float) $coupon->amount / 100);
        } elseif ($coupon->type === 'fixed') {
            $discount = (float) $coupon->amount;
        } elseif ($coupon->type === 'trial') {
            $discount = $subtotal;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return $discount;
    }

    /**
     * Monta a resposta padrão de pedido para o front.
     */
    private function buildOrderSummary(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            $usageConfig = null;
            $configItem = $item->configurations->firstWhere('key', 'usage');
            if ($configItem) {
                $usageConfig = $configItem->value;
            } elseif (is_array($item->pricing_model_snapshot ?? null) && isset($item->pricing_model_snapshot['usage'])) {
                $usageConfig = $item->pricing_model_snapshot['usage'];
            }

            return [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_name' => $item->item_name_snapshot,
                'item_key' => $item->item_key,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price_snapshot,
                'subtotal' => $item->amount,
                'pricing_model_snapshot' => $item->pricing_model_snapshot,
                'usage' => $usageConfig,
            ];
        });

        $subtotalAmount = (float) $order->items()->sum('amount');
        $discountAmount = (float) ($order->coupon_discount_amount ?? 0);

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'current_step' => $order->current_step,
            'amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $order->total(),
            'currency' => $order->currency,
            'coupon' => $order->coupon_id ? [
                'code' => $order->coupon_code_snapshot,
                'type' => $order->coupon_type_snapshot,
                'value' => $order->coupon_value_snapshot,
                'trial_months' => $order->coupon_trial_months,
                'discount_amount' => $discountAmount,
            ] : null,
            'items' => $items,
        ];
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

    /**
     * Cria um pedido em rascunho (intenção de compra) com base nos módulos desejados.
     * Espera `modules` como array de objetos { id, config }.
     */
    public function orderUpdate(Request $request)
    {

        // Obtém dados
        $data = $request->all();

        // Extrai cliente
        $client = $data['client'];

        // Cria ou atualiza o rascunho com os módulos enviados
        $order = $this->orderService->getOrderInProgress($client);

        // Realiza ação desejada
        $action = match($data['action']) {
            'change_module' => $this->toggleModule($order, $data['value']),
            'step'          => $this->updateStep($order, $data['value']),
            default => null,
        };

        // Retorna resposta
        return response()->json([
            'message' => $action['message'],
            'order' => $order
        ]);

        return ($order);

     
    }

    /**
     * Atualiza o passo do pedido
     */
    private function updateStep($order, $step){
        
        // Atualiza a etapa
        $order->current_step = $step;
        $order->save();
        
        return [
            'message' => 'Passo atualizado com sucesso',
            'order' => $order
        ];
        
    }

    /**
     * Adiciona ou remove o módulo
     */
    private function toggleModule($order, $moduleId){

        /**
         * Busca dados do módulo
         */
        $module = Module::find($moduleId);

        /**
         * Verifica se esse pedido já tem esse item
         */
        $existingItem = OrderItem::where('order_id', $order->id)
                                    ->where('type', 'Módulo')
                                    ->where('item_key', $moduleId)
                                    ->first();

        /**
         * Se o módulo já existe, remove
         */
        if ($existingItem) {

            // Apaga o item
            $existingItem->delete();

            // Recalcula os totais
            $this->orderService->recalculateOrderTotals($order);

            // Retorno
            return [
                'message' => 'Módulo removido com sucesso.',
                'action'  => 'removed',
            ];
        }

        // Cria item de módulo no pedido
        OrderItem::create([
            'order_id'                  => $order->id,
            'type'                      => 'Módulo',
            'item_name'                 => $module->name,
            'item_key'                  => $module->id,
            'item_billing_type'         => $module->pricing_type,
            'item_usage_quantity'       => 1,
            'item_value'                => $module->value,
            'amount'                    => $module->value,
            'payload'                   => json_encode($module->toArray()),
        ]);

        // Recalcula os totais
        $this->orderService->recalculateOrderTotals($order);

        // Retorno
        return [
            'message' => 'Módulo adicionado com sucesso.',
            'action'  => 'added',
        ];

    }

    /**
     * Função responsável por processar pagamentos dos sistemas miCores.
     * Utilizamos junto a ele a integração através da eRede.
     */
    public function orderPayment(Request $request, OrderService $service) {

        // Obtém os dados enviados no formulário
        $data = $request->all();

        // Verifica se veio o id do pedido
        if(isset($data['order_id'])) {

            // Obtem o pedido
            $order = Order::find($data['order_id']);

            // Se ele existir atualiza para pendente
            if($order) {
                $order->update([
                    'status' => 'pending'
                ]);
            }

        } else {
            $order = Order::where('client_id', $data['client']->id)->orderBy('id', 'DESC')->first();
        }

        // Verifica se existe o cartão
        if(isset($data['card']) && isset($data['card']['card_id'])) {
            $cardId = $data['card']['card_id'];
        }

        // Encontra o cartão do cliente para reutilizar
        if(isset($cardId)){

            // Encontra o cartão do cliente
            $card = ClientCard::where('client_id', $data['client']->id)->where('id', $cardId)->first();

            // Se não encontrar o cliente
            if(!$card) return response()->json(['message' => 'Cartão não encontrado para esse cliente'], 404);

        } else if(isset($data['card']) && (!isset($data['card']['name']) || !isset($data['card']['number']) || !isset($data['card']['expiration']) || !isset($data['card']['cvv']))) {
            return response()->json(['message' => 'Parâmetros faltando'], 400);
        }
        
        // Verifica se veio todos os dados do cartão
        if (isset($data['card']) && (isset($data['card']['name']) && isset($data['card']['number']) && isset($data['card']['expiration']) && isset($data['card']['cvv']))) {

            // Limpa os dados do cartão
            $data['card']['number'] = (int) str_replace(' ', '', $data['card']['number']);
    
            // Busca o cartão do cliente
            $card = ClientCard::where('client_id', $data['client']->id)->where('number', $data['card']['number'])->first();

            if(!$card) {
                // Salvamos o cartão do cliente
                $card = ClientCard::create([
                    'client_id'        => $data['client']->id,
                    'main'             => true,
                    'name'             => $data['card']['name'],
                    'number'           => $data['card']['number'],
                    'expiration_month' => substr($data['card']['expiration'], 0, 2),
                    'expiration_year' => '20' . substr($data['card']['expiration'], -2),
                ]);
            }

        }

        // Retorna o cliente atualizado
        $response = $service->createOrderPayment($order, $data['client'], $card, $data['card']['cvv'] ?? null, $data['billing_cycle'], $data['card']['address']);

        return response()->json([
            'message' => $response
        ], 200);

    }

}
