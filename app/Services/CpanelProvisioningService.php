<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantProvisioning;
use App\Models\Package;
use Exception;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use RuntimeException;

class CpanelProvisioningService
{
    private string $cpanelUrl;
    private string $cpanelUser;
    private string $cpanelPass;
    private string $cpanelPrefix;

    public function __construct(private ModuleService $moduleService)
    {
        $this->cpanelUrl = (string) env('CPANEL_URL');
        $this->cpanelUser = (string) env('CPANEL_USER');
        $this->cpanelPass = (string) env('CPANEL_PASS');
        $this->cpanelPrefix = (string) env('CPANEL_PREFIX');
    }

    /**
     * Executa o fluxo de provisionamento da próxima etapa do cliente.
     */
    public function runProvisioning(Tenant|int $clientInput): array
    {
        $tenant = $this->resolveTenant($clientInput);
        $provisioning = $this->getTenantProvisioningOrFail($tenant);

        if ($provisioning->install === TenantProvisioning::STEP_SUBDOMAIN) {
            return $this->handleSubdomainStep($tenant, $provisioning);
        }

        if ($provisioning->install === TenantProvisioning::STEP_DATABASE) {
            return $this->handleDatabaseStep($tenant, $provisioning);
        }

        if ($provisioning->install === TenantProvisioning::STEP_USER_TOKEN) {
            return $this->handleUserTokenStep($tenant, $provisioning);
        }

        if ($provisioning->install === TenantProvisioning::STEP_MODULES) {
            return $this->handleModulesStep($tenant, $provisioning);
        }

        $provisioning->install = TenantProvisioning::STEP_COMPLETED;
        $provisioning->save();

        return [
            'url' => $this->getTenantPrimaryDomainOrFail($tenant),
            'message' => 'Conta criada com sucesso',
            'step' => TenantProvisioning::STEP_COMPLETED,
        ];
    }

    /**
     * Cria o subdomínio do cliente manualmente.
     */
    public function createSubdomainForTenant(int $clientId): array
    {
        $tenant = $this->resolveTenant($clientId);
        $domain = $this->getTenantPrimaryDomainOrFail($tenant);

        return $this->createSubdomain($domain);
    }

    /**
     * Clona o banco template para o cliente manualmente.
     */
    public function cloneDatabaseForTenant(int $clientId): array
    {
        $tenant = $this->resolveTenant($clientId);

        return $this->cloneDatabase($tenant);
    }

    /**
     * Insere usuário inicial e token no banco do cliente manualmente.
     */
    public function addTokenAndUserForTenant(int $clientId): array
    {
        $tenant = $this->resolveTenant($clientId);
        $this->insertTokenAndUser($tenant);

        return [
            'message' => 'Usuário e token inseridos com sucesso',
            'step' => TenantProvisioning::STEP_MODULES,
        ];
    }

    private function handleSubdomainStep(Tenant $tenant, TenantProvisioning $provisioning): array
    {
        $domain = $this->getTenantPrimaryDomainOrFail($tenant);

        Log::info('Criando subdomínio do cliente', ['tenant_id' => $tenant->id, 'domain' => $domain]);
        $this->createSubdomain($domain);

        $provisioning->install = TenantProvisioning::STEP_DATABASE;
        $provisioning->save();

        return [
            'message' => 'Subdomínio criado com sucesso',
            'step' => TenantProvisioning::STEP_DATABASE,
        ];
    }

    private function handleDatabaseStep(Tenant $tenant, TenantProvisioning $provisioning): array
    {
        Log::info('Clonando banco template para cliente', [
            'tenant_id' => $tenant->id,
            'database' => $provisioning->table,
        ]);

        $this->cloneDatabase($tenant);

        $provisioning->install = TenantProvisioning::STEP_USER_TOKEN;
        $provisioning->save();

        return [
            'message' => 'Banco de dados clonado com sucesso',
            'step' => TenantProvisioning::STEP_USER_TOKEN,
        ];
    }

    private function handleUserTokenStep(Tenant $tenant, TenantProvisioning $provisioning): array
    {
        Log::info('Inserindo usuário e token no banco do cliente', [
            'tenant_id' => $tenant->id,
            'database' => $provisioning->table,
        ]);

        $this->insertTokenAndUser($tenant);

        $provisioning->install = TenantProvisioning::STEP_MODULES;
        $provisioning->save();

        return [
            'message' => 'Usuário e token inseridos com sucesso',
            'step' => TenantProvisioning::STEP_MODULES,
        ];
    }

    private function handleModulesStep(Tenant $tenant, TenantProvisioning $provisioning): array
    {
        Log::info('Configurando módulos do cliente', [
            'tenant_id' => $tenant->id,
            'database' => $provisioning->table,
        ]);

        $this->configureModulesForTenant($tenant);

        $provisioning->install = TenantProvisioning::STEP_FINALIZING;
        $provisioning->save();

        return [
            'message' => 'Módulos configurados com sucesso',
            'step' => TenantProvisioning::STEP_FINALIZING,
        ];
    }

    private function resolveTenant(Tenant|int $clientInput): Tenant
    {
        $tenant = $clientInput instanceof Tenant
            ? $clientInput->loadMissing('provisioning', 'domains')
            : Tenant::with(['provisioning', 'domains'])->find($clientInput);

        if (!$tenant) {
            throw new RuntimeException('Tenante não encontrado para provisionamento.');
        }

        return $tenant;
    }

    private function getTenantProvisioningOrFail(Tenant $tenant): TenantProvisioning
    {
        $provisioning = $tenant->provisioning;

        if (!$provisioning) {
            throw new RuntimeException("Provisioning não encontrado para o cliente {$tenant->id}.");
        }

        return $provisioning;
    }

    private function getTenantPrimaryDomainOrFail(Tenant $tenant): string
    {
        $domain = $tenant->domains[0]->domain ?? null;

        if (!$domain) {
            throw new RuntimeException("Domínio principal não encontrado para o cliente {$tenant->id}.");
        }

        return $domain;
    }

    private function insertTokenAndUser(Tenant $tenant): void
    {
        $provisioning = $this->getTenantProvisioningOrFail($tenant);

        $tenantDatabase = [
            'name' => $provisioning->table,
            'user' => $provisioning->table_user,
            'password' => $provisioning->table_password,
        ];

        $firstUser = $provisioning->first_user;

        $this->connectTenantDatabase($tenantDatabase);

        DB::connection('mysql_cliente')->table('users')->insert([
            'name' => $firstUser['short_name'],
            'password' => Hash::make($firstUser['password']),
            'full_name' => $firstUser['name'],
            'email' => $firstUser['email'],
            'role_id' => 1,
            'created_by' => 1,
        ]);

        DB::connection('mysql_cliente')->table('collaborators')->insert([
            'user_id' => 2,
            'created_by' => 1,
        ]);

        DB::connection('mysql_cliente')->table('stores')->insert([
            'public' => 1,
            'company' => 'Matriz',
            'abbreviation' => 'MT',
            'cnpj' => '00000000000000',
            'name_fantasy' => 'Matrix',
            'address' => 'Rua Teste',
            'number' => '123',
            'neighborhood' => 'Teste',
            'zip' => '00000000',
            'email' => 'matrix@micore.com.br',
            'site' => 'matrix.com.br',
            'phone1' => '123456789',
            'status' => 1,
            'country_id' => 26,
            'state_id' => 1,
            'city_id' => 1,
            'created_by' => 1,
        ]);

        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name' => 'tenant',
            'option_value' => $tenant->id,
        ]);

        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name' => 'token',
            'option_value' => $tenant->token,
        ]);

        $sizeStorage = (int) optional(Package::find(1))->size_storage;

        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name' => 's3StorageAllow',
            'option_value' => $sizeStorage,
        ]);
    }

    private function configureModulesForTenant(Tenant $tenant): void
    {
        $plan = $tenant->plan;

        $this->moduleService->configureModules(
            $tenant,
            $plan ? $plan->items()->pluck('item_id')->toArray() : [],
            true
        );

        $this->moduleService->createSubscriptionCore(
            $tenant,
            now()->toDateString(),
            now()->addDays(30)->toDateString()
        );

        $usersLimit = $plan->users_limit;
        $sizeStorage = $plan->size_storage;

        $this->moduleService->updateUsersLimitsCore($tenant, $usersLimit);
        $this->moduleService->updateSizeStorageCore($tenant, $sizeStorage);
    }

    private function connectTenantDatabase(array $tenantDatabase): void
    {
        config([
            'database.connections.mysql_cliente' => [
                'driver' => 'mysql',
                'host' => env('WHM_IP'),
                'database' => $tenantDatabase['name'],
                'username' => $tenantDatabase['user'],
                'password' => $tenantDatabase['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ],
        ]);

        DB::purge('mysql_cliente');
        DB::disconnect('mysql_cliente');
        DB::reconnect('mysql_cliente');
    }

    private function createSubdomain(string $domain): array
    {
        $documentRoot = '/home/' . $this->cpanelPrefix . '/public_html';

        return $this->requestCpanelApi('GET', "{$this->cpanelUrl}/execute/SubDomain/addsubdomain", [
            'domain' => $domain,
            'rootdomain' => 'micore.com.br',
            'dir' => $documentRoot,
        ]);
    }

    /**
     * @throws Exception
     */
    private function cloneDatabase(Tenant $tenant): array
    {
        $provisioning = $this->getTenantProvisioningOrFail($tenant);

        $database = [
            'name' => $provisioning->table,
            'user' => $provisioning->table_user,
            'password' => $provisioning->table_password,
        ];

        $templateDatabase = $this->cpanelPrefix . '_template';

        $this->requestCpanelApi('GET', "{$this->cpanelUrl}/execute/Mysql/create_database", [
            'name' => $database['name'],
        ]);

        $ssh = new SSH2((string) env('WHM_IP'));
        $keyPath = storage_path('keys/id_rsa');
        $privateKey = PublicKeyLoader::loadPrivateKey(file_get_contents($keyPath), env('SSH_PASSPHRASE'));

        if (!$ssh->login($this->cpanelUser, $privateKey)) {
            throw new Exception('Falha na autenticação SSH com chave privada');
        }

        $cloneCommand = "mysqldump -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$templateDatabase} | mysql -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$database['name']}";
        $ssh->exec($cloneCommand);

        $this->requestCpanelApi('GET', "{$this->cpanelUrl}/execute/Mysql/create_user", [
            'name' => $database['user'],
            'password' => $database['password'],
        ]);

        $this->requestCpanelApi('GET', "{$this->cpanelUrl}/execute/Mysql/set_privileges_on_database", [
            'user' => $database['user'],
            'database' => $database['name'],
            'privileges' => 'ALL PRIVILEGES',
        ]);

        return [
            'message' => "Banco de dados {$database['name']} clonado com sucesso!",
            'usuario' => $database['user'],
            'password' => $database['password'],
        ];
    }

    private function requestCpanelApi(string $method, string $url, ?array $query = null): array
    {
        $tenant = new Guzzle();

        $options = [
            'auth' => [$this->cpanelUser, $this->cpanelPass],
        ];

        if ($query !== null) {
            $options['query'] = $query;
        }

        $response = $tenant->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
