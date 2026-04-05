<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientProvisioning;
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
    public function runProvisioning(Client|int $clientInput): array
    {
        $client = $this->resolveClient($clientInput);
        $provisioning = $this->getClientProvisioningOrFail($client);

        if ($provisioning->install === ClientProvisioning::STEP_SUBDOMAIN) {
            return $this->handleSubdomainStep($client, $provisioning);
        }

        if ($provisioning->install === ClientProvisioning::STEP_DATABASE) {
            return $this->handleDatabaseStep($client, $provisioning);
        }

        if ($provisioning->install === ClientProvisioning::STEP_USER_TOKEN) {
            return $this->handleUserTokenStep($client, $provisioning);
        }

        if ($provisioning->install === ClientProvisioning::STEP_MODULES) {
            return $this->handleModulesStep($client, $provisioning);
        }

        $provisioning->install = ClientProvisioning::STEP_COMPLETED;
        $provisioning->save();

        return [
            'url' => $this->getClientPrimaryDomainOrFail($client),
            'message' => 'Conta criada com sucesso',
            'step' => ClientProvisioning::STEP_COMPLETED,
        ];
    }

    /**
     * Cria o subdomínio do cliente manualmente.
     */
    public function createSubdomainForClient(int $clientId): array
    {
        $client = $this->resolveClient($clientId);
        $domain = $this->getClientPrimaryDomainOrFail($client);

        return $this->createSubdomain($domain);
    }

    /**
     * Clona o banco template para o cliente manualmente.
     */
    public function cloneDatabaseForClient(int $clientId): array
    {
        $client = $this->resolveClient($clientId);

        return $this->cloneDatabase($client);
    }

    /**
     * Insere usuário inicial e token no banco do cliente manualmente.
     */
    public function addTokenAndUserForClient(int $clientId): array
    {
        $client = $this->resolveClient($clientId);
        $this->insertTokenAndUser($client);

        return [
            'message' => 'Usuário e token inseridos com sucesso',
            'step' => ClientProvisioning::STEP_MODULES,
        ];
    }

    private function handleSubdomainStep(Client $client, ClientProvisioning $provisioning): array
    {
        $domain = $this->getClientPrimaryDomainOrFail($client);

        Log::info('Criando subdomínio do cliente', ['client_id' => $client->id, 'domain' => $domain]);
        $this->createSubdomain($domain);

        $provisioning->install = ClientProvisioning::STEP_DATABASE;
        $provisioning->save();

        return [
            'message' => 'Subdomínio criado com sucesso',
            'step' => ClientProvisioning::STEP_DATABASE,
        ];
    }

    private function handleDatabaseStep(Client $client, ClientProvisioning $provisioning): array
    {
        Log::info('Clonando banco template para cliente', [
            'client_id' => $client->id,
            'database' => $provisioning->table,
        ]);

        $this->cloneDatabase($client);

        $provisioning->install = ClientProvisioning::STEP_USER_TOKEN;
        $provisioning->save();

        return [
            'message' => 'Banco de dados clonado com sucesso',
            'step' => ClientProvisioning::STEP_USER_TOKEN,
        ];
    }

    private function handleUserTokenStep(Client $client, ClientProvisioning $provisioning): array
    {
        Log::info('Inserindo usuário e token no banco do cliente', [
            'client_id' => $client->id,
            'database' => $provisioning->table,
        ]);

        $this->insertTokenAndUser($client);

        $provisioning->install = ClientProvisioning::STEP_MODULES;
        $provisioning->save();

        return [
            'message' => 'Usuário e token inseridos com sucesso',
            'step' => ClientProvisioning::STEP_MODULES,
        ];
    }

    private function handleModulesStep(Client $client, ClientProvisioning $provisioning): array
    {
        Log::info('Configurando módulos do cliente', [
            'client_id' => $client->id,
            'database' => $provisioning->table,
        ]);

        $this->configureModulesForClient($client);

        $provisioning->install = ClientProvisioning::STEP_FINALIZING;
        $provisioning->save();

        return [
            'message' => 'Módulos configurados com sucesso',
            'step' => ClientProvisioning::STEP_FINALIZING,
        ];
    }

    private function resolveClient(Client|int $clientInput): Client
    {
        $client = $clientInput instanceof Client
            ? $clientInput->loadMissing('provisioning', 'domains')
            : Client::with(['provisioning', 'domains'])->find($clientInput);

        if (!$client) {
            throw new RuntimeException('Cliente não encontrado para provisionamento.');
        }

        return $client;
    }

    private function getClientProvisioningOrFail(Client $client): ClientProvisioning
    {
        $provisioning = $client->provisioning;

        if (!$provisioning) {
            throw new RuntimeException("Provisioning não encontrado para o cliente {$client->id}.");
        }

        return $provisioning;
    }

    private function getClientPrimaryDomainOrFail(Client $client): string
    {
        $domain = $client->domains[0]->domain ?? null;

        if (!$domain) {
            throw new RuntimeException("Domínio principal não encontrado para o cliente {$client->id}.");
        }

        return $domain;
    }

    private function insertTokenAndUser(Client $client): void
    {
        $provisioning = $this->getClientProvisioningOrFail($client);

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
            'option_value' => $client->id,
        ]);

        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name' => 'token',
            'option_value' => $client->token,
        ]);

        $sizeStorage = (int) optional(Package::find(1))->size_storage;

        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name' => 's3StorageAllow',
            'option_value' => $sizeStorage,
        ]);
    }

    private function configureModulesForClient(Client $client): void
    {
        $package = $client->package;

        $this->moduleService->configureModules(
            $client,
            $package ? $package->items()->pluck('item_id')->toArray() : [],
            true
        );

        $this->moduleService->createSubscriptionCore(
            $client,
            now()->toDateString(),
            now()->addDays(30)->toDateString()
        );
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
    private function cloneDatabase(Client $client): array
    {
        $provisioning = $this->getClientProvisioningOrFail($client);

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
        $client = new Guzzle();

        $options = [
            'auth' => [$this->cpanelUser, $this->cpanelPass],
        ];

        if ($query !== null) {
            $options['query'] = $query;
        }

        $response = $client->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
