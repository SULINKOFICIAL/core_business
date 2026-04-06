<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantProvisioning;
use App\Models\ErrorMiCore;
use App\Models\IntegrationSuggestion;
use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Services\CpanelProvisioningService;
use App\Services\SystemProblemNotificationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
    private $repository;
    private $cpanelProvisioningService;

    public function __construct(
        Tenant $content,
        CpanelProvisioningService $cpanelProvisioningService
    )
    {
        $this->repository = $content;
        $this->cpanelProvisioningService = $cpanelProvisioningService;
    }

    /**
     * Função responsável por criar clientes através de sites externos,
     * por exemplo o site comercial micore.com.br, mas também pode ser
     * utilizado para criar sistemas através de landing pages.
     */
    public function newTenant(Request $request){

        // Obtém dados
        $data = $this->mapOnboardingPayload($request->all());
        $mainGoals = $data['main_goals'] ?? [];
        unset($data['main_goals']);

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
        $data['whatsapp'] = !empty($data['whatsapp']) ? onlyNumbers($data['whatsapp']) : null;

        // Realiza verificações de duplicidade
        foreach ($verifications as $field => $message) {
            if (!empty($data[$field])) {
                if ($tenant = Tenant::where($field, $data[$field])->first()) {
                    return response()->json([
                        'message' => $message,
                        'url'     => $tenant->domain,
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

        $provisioningData = [
            'table' => $data['table'],
            'table_user' => $data['table_usr'],
            'table_password' => $data['table_password'],
            'first_user' => $data['first_user'],
            'install' => TenantProvisioning::STEP_SUBDOMAIN,
        ];

        unset($data['table'], $data['table_usr'], $data['table_password'], $data['first_user'], $data['password']);

        // Insere no banco de dados
        $tenant = $this->repository->create($data);
        if (!empty($mainGoals)) {
            $tenant->mainGoals()->createMany(array_map(function ($goal) {
                return ['goal' => $goal];
            }, $mainGoals));
        }
        $tenant->provisioning()->create($provisioningData);
        $tenant->runtimeStatus()->create();

        // Simula solicitação de troca de pacote
        $request = new Request(['package_id' => 1]);

        // Adiciona pacote básico ao cliente
        app(PackageController::class)->assign($request, $tenant->id);

        // Gera subdomínio, banco de dados e usuário no Cpanel miCore.com.br
        return response()->json($this->cpanelProvisioningService->runProvisioning($tenant));

    }

    /**
     * Normaliza payload vindo do onboarding para o formato esperado na central.
     * Mantém compatibilidade com integrações antigas e novas chaves do formulário.
     */
    private function mapOnboardingPayload(array $data): array
    {
        $allowedMainGoals = [
            'centralizar_atendimentos',
            'vender_online',
            'controlar_estoque',
            'vender_servicos',
        ];

        if (empty($data['name']) && !empty($data['full_name'])) {
            $data['name'] = $data['full_name'];
        }

        if (empty($data['whatsapp']) && !empty($data['phone'])) {
            $data['whatsapp'] = $data['phone'];
        }

        if (empty($data['company']) && !empty($data['name'])) {
            // Fallback para não quebrar criação quando onboarding não enviar razão social.
            $data['company'] = $data['name'];
        }

        if (!empty($data['main_goal']) && empty($data['main_goals'])) {
            $data['main_goals'] = [$data['main_goal']];
        }

        if (isset($data['main_goals']) && !is_array($data['main_goals'])) {
            $data['main_goals'] = [$data['main_goals']];
        }

        $data['main_goals'] = array_values(array_unique(array_filter(
            $data['main_goals'] ?? [],
            function ($goal) use ($allowedMainGoals) {
                return in_array($goal, $allowedMainGoals, true);
            }
        )));
        unset($data['main_goal']);

        $data['tips_whatsapp'] = !empty($data['tips_whatsapp']);
        $data['tips_email'] = !empty($data['tips_email']);
        $data['has_coupon'] = !empty($data['has_coupon']);
        if (!empty($data['document_type']) && in_array($data['document_type'], ['cnpj', 'cpf'], true)) {
            $data['document_type'] = $data['document_type'];
        } else {
            // Compatibilidade com payload antigo que enviava checkbox no_cnpj.
            $data['document_type'] = !empty($data['no_cnpj']) ? 'cpf' : 'cnpj';
        }
        unset($data['no_cnpj']);

        if (!empty($data['company_zip_code'])) {
            $data['company_zip_code'] = onlyNumbers($data['company_zip_code']);
        }

        return $data;
    }

    /**
     * Função responsável por obter o cliente no banco de dados
     * e pegar o dominio do cliente para acessar via micore.com.br
     */
    public function findTenant(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Obtém dados do cliente
        $tenant = isset($data['email']) ? Tenant::where('email', $data['email'])->first()
                : (isset($data['cnpj']) ? Tenant::where('cnpj', $data['cnpj'])->first()
                : (isset($data['cpf']) ? Tenant::where('cpf', $data['cpf'])->first()
                : null));

        // Verifica se o cliente foi encontrado
        if ($tenant) {
            $domain = $tenant->domains()->where('status', true)->first()?->domain;

            if (!$domain) {
                return response()->json(['message' => 'Tenante encontrado, mas sem domínio ativo vinculado.'], 404);
            }

            return response()->json(['domain' => $domain]);
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
        $domain = TenantDomain::where('domain', $domain)->first();

        // Verifica se o domínio existe
        if (!$domain) return response()->json(['error' => 'Domínio não encontrado.'], 404);

        // Busca o banco de dados correspondente ao subdomínio
        $tenant = $domain->client;

        // Evita erro quando existir domínio órfão (sem cliente relacionado)
        if (!$tenant) {
            Log::warning('Domínio sem cliente vinculado na API getDatabase', [
                'domain_id' => $domain->id,
                'tenant_id' => $domain->tenant_id,
                'domain' => $domain->domain,
            ]);

            return response()->json(['error' => 'Domínio sem cliente vinculado.'], 404);
        }

        // Retorna os dados do banco de dados
        return response()->json([
            'tenant'        => $tenant->id,
            'db_name'       => $tenant->provisioning?->table,
            'db_user'       => $tenant->provisioning?->table_user,
            'db_password'   => $tenant->provisioning?->table_password,
        ]);

    }

    public function notifyErrors(Request $request, SystemProblemNotificationService $systemProblemNotificationService)
    {
        // Valida os campos esperados para persistência e notificação.
        $data = $request->validate([
            'url' => ['nullable', 'string', 'max:1000'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'message' => ['required', 'string', 'max:5000'],
            'stack_trace' => ['nullable', 'string'],
            'status_code' => ['nullable', 'integer'],
            'system_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'array'],
        ]);

        // Prioriza o cliente resolvido pelo middleware para evitar spoofing.
        $tenant = $request->input('client');
        $data['tenant_id'] = $tenant->id ?? ($data['tenant_id'] ?? null);

        // Registra erro que veio através do MiCore.
        $error = ErrorMiCore::create($data);

        // Dispara notificação consolidada por e-mail e template de WhatsApp.
        $notification = $systemProblemNotificationService->notify(array_merge($data, [
            'error_id' => $error->id,
            'event_date' => $data['event_date'] ?? now()->format('d/m/Y H:i:s'),
        ]));

        return response()->json([
            'message' => 'Registrou o erro',
            'id' => $error->id,
            'notification' => $notification,
        ], 201);
    }

    public function suggestions(Request $request) {

        // Recebe dados
        $data = $request->all();

        // Registra a sugestão no banco de dados
        IntegrationSuggestion::create($data);

        // Retorna resposta
        return response()->json('Sugestão enviada com sucesso!', 201);

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
