<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Package;
use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;

class CpanelController extends Controller
{
    protected $request;
    private $cpanelHost;
    private $cpanelUrl;
    private $cpanelUser;
    private $cpanelPass;
    private $cpanelPrefix;

    public function __construct()
    {
        // Define as configurações do cPanel
        $this->cpanelHost   = env('CPANEL_HOST');
        $this->cpanelUrl    = env('CPANEL_URL');
        $this->cpanelUser   = env('CPANEL_USER');
        $this->cpanelPass   = env('CPANEL_PASS');
        $this->cpanelPrefix = env('CPANEL_PREFIX');
    }

    /**
     * Cria um subdomínio e um banco de dados associado.
     *
     * @param string $domain Nome do subdomínio
     * @param string $table Nome da tabela (não utilizada atualmente)
     * @return \Illuminate\Http\JsonResponse
     */
    public function make($id)
    {

        // Obtém cliente
        $client = Client::find($id);

        // Se o cliente estiver na etapa de criação
        if($client->install == 1){

            // Registra tempo
            Log::info("Criando subdomínio: " . $client->domains[0]->domain);

            // Cria o subdomínio
            $this->makeSubdomain($client->domains[0]->domain);
            
            // Atualiza status
            $client->install = 2;
            $client->save();

            return response()->json([
                'message' => 'Subdomínio criado com sucesso',
                'step' => 2
            ]);
        }

        // Se o cliente estiver na etapa de clonar o banco de dados modelo
        if($client->install == 2){
            
            // Registra tempo
            Log::info("Clonando banco template para : " . $client->table);

            // Cria o banco de dados
            $this->cloneDatabase($client);
            
            // Atualiza status
            $client->install = 3;
            $client->save();

            return response()->json([
                'message' => 'Banco de dados clonado com sucesso',
                'step' => 3
            ]);
        }

        // Se o cliente estiver na etapa de inserir o usuário e token
        if($client->install == 3){
        
            // Registra tempo
            Log::info("Inserindo usuário e token no banco : " . $client->table);

            // Adiciona registros únicos no cliente
            $this->addTokenAndUser($client);
    
            // Atualiza status
            $client->install = 4;
            $client->save();

            // Registra tempo
            Log::info("Finalizou a inserção dos usuário e token no banco : " . $client->table);

            return response()->json([
                'message' => 'Usuário e token inseridos com sucesso',
                'step' => 4
            ]);
        }

        // Atualiza status
        $client->install = 5;
        $client->save();

        // Retorna a página
        return response()->json([
            'url' => $client->domains[0]->domain,
            'message' => 'Conta criada com sucesso',
            'step' => 5
        ]);

    }
    


    /**
     * Cria um subdomínio via API do cPanel.
     *
     * @param string $domain Nome do subdomínio a ser criado
     * @return array Resposta da API do cPanel
     */
    public function clientAddTokenAndUser($id)
    {

        // Obtém cliente
        $client = Client::find($id);
        
        // Obtém banco de dados
        $database = [
            'name' => $client->table,
            'password' => $client->table_password,
        ];

        // Usuário
        $user = [
            'short_name' => 'teste',
            'name'       => 'teste',
            'email'      => 'teste@gmail.com',
            'password'   => 'teste',
        ];

        // Envia a solicitação para criar o subdomínio
        $response = $this->addTokenAndUser($database, $user, $client->token);

        // Retorna a resposta da API
        return $response;

    }

    /**
     * Adiciona token e usuário no banco de dados do cliente.
     */
    private function addTokenAndUser($client)
    {

        // Banco de dados
        $database = [
            'name' => $client->table,
            'user' => $client->table_user,
            'password' => $client->table_password,
        ];

        // Obtém cliente
        $user = $client->first_user;

        // Conectar ao banco recém-criado
        $this->connectDatabase($database);

        // Gerar senha hashada e token de API
        $userPassword = Hash::make($user['password']);

        /**
         * Por padrão cria o usuário de sistema para atribuir a ele
         * configurações e históricos gerados pelo sistema.
         */
        DB::connection('mysql_cliente')->table('users')->insert([
            'name'       => 'Sistema',
            'password'   => Hash::make(rand(100000, 999999)),
            'full_name'  => 'Sistema',
            'email'      => 'sistema@micore.com.br',
            'role_id'    => 1,
            'created_by' => 1,
        ]);

        /**
         * Inserir o primeiro usuário que utilizará o sistema.
         */
        DB::connection('mysql_cliente')->table('users')->insert([
            'name'       => $user['short_name'],
            'password'   => $userPassword,
            'full_name'  => $user['name'],
            'email'      => $user['email'],
            'role_id'    => 1,
            'created_by' => 1,
        ]);

        /**
         * Inserir o colaborador referente ao usuário.
         */
        DB::connection('mysql_cliente')->table('collaborators')->insert([
            'user_id'    => 2,
            'created_by' => 1,
        ]);

        /**
         * Inserir o colaborador referente ao usuário.
         */
        DB::connection('mysql_cliente')->table('stores')->insert([
            'public'        => 1,
            'company'       => 'Matriz',
            'abbreviation'  => 'MT',
            'cnpj'          => '00000000000000',
            'name_fantasy'  => 'Matrix',
            'address'       => 'Rua Teste',
            'number'        => '123',
            'neighborhood'  => 'Teste',
            'zip'           => '00000000',
            'email'         => 'matrix@micore.com.br',
            'site'          => 'matrix.com.br',
            'phone1'        => '123456789',
            'status'        => 1,
            'country_id'    => 26,
            'state_id'      => 1,
            'city_id'       => 1,
            'created_by'    => 1,
        ]);

        /**
         * Inserir o token da conta do usuário, é necessário para
         * conseguirmos fazermos algumas separações como de Cache.
         */
        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name'  => 'tenant',
            'option_value' => $client->id,
        ]);

        /**
         * Inserir o token da conta do usuário, é necessário para
         * realizarmos ações via API atraves da central.
         */
        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name'  => 'token',
            'option_value' => $client->token,
        ]);

        // Obtém o tamanho do pacote inicial
        $sizeStorage = (int) Package::find(1)->size_storage;

        // Inserir a quantia inicial de armazenamento
        DB::connection('mysql_cliente')->table('central_configs')->insert([
            'option_name'  => 's3StorageAllow',
            'option_value' => $sizeStorage,
        ]);

        return true;
    }

    /**
     * Conecta ao banco de dados do cliente.
     */
    private function connectDatabase($datatable)
    {
     
        // Configura conexão
        config([
            'database.connections.mysql_cliente' => [
                'driver'    => 'mysql',
                'host'      => env('WHM_IP'),
                'database'  => $datatable['name'],
                'username'  => $datatable['user'],
                'password'  => $datatable['password'],
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            ]
        ]);

        // Limpa conexões antigas
        DB::purge('mysql_cliente'); 

        // Força desconexão anterior
        DB::disconnect('mysql_cliente');

        // Reconecta ao novo banco
        DB::reconnect('mysql_cliente');
    
        return true;
        
    }

    /**
     * Cria um subdomínio via API do cPanel.
     *
     * @param string $domain Nome do subdomínio a ser criado
     * @return array Resposta da API do cPanel
     */
    public function clientMakeDomain($id)
    {

        // Obtém cliente
        $client = Client::find($id);

        // Envia a solicitação para criar o subdomínio
        $response = $this->makeSubdomain($client->domain);

        // Retorna a resposta da API
        return $response;

    }

    /**
     * Cria um subdomínio via API do cPanel.
     *
     * @param string $domain Nome do subdomínio a ser criado
     * @return array Resposta da API do cPanel
     */
    private function makeSubdomain($domain)
    {

        $documentRoot = "/home/" . $this->cpanelPrefix . "/public_html";

        // Envia a solicitação para criar o subdomínio
        $response = $this->guzzle('GET', "{$this->cpanelUrl}/execute/SubDomain/addsubdomain", $this->cpanelUser, $this->cpanelPass, [
            "domain" => $domain,
            "rootdomain" => "micore.com.br",
            "dir" => $documentRoot
        ]);

        // Retorna a resposta da API
        return $response;

    }


    /**
     * Cria um subdomínio via API do cPanel.
     *
     * @param string $domain Nome do subdomínio a ser criado
     * @return array Resposta da API do cPanel
     */
    public function clientMakeDatabase($id)
    {

        // Obtém cliente
        $client = Client::find($id);
        
        // Envia a solicitação para criar o subdomínio
        $response = $this->cloneDatabase($client);

        // Retorna a resposta da API
        return $response;

    }

    /**
     * Clona um banco de dados existente (micorecom_template) para um novo banco.
     *
     * @param string $novoBanco Nome do novo banco de dados
     * @return void
     * @throws Exception Se a clonagem falhar
     */
    private function cloneDatabase($client)
    {

        // Banco de dados
        $database = [
            'name' => $client->table,
            'user' => $client->table_user,
            'password' => $client->table_password,
        ];

        // Banco modelo
        $templateBanco = $this->cpanelPrefix . '_template';

        // Criar o novo banco de dados
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_database", $this->cpanelUser, $this->cpanelPass, [
            "name" => $database['name']
        ]);

        // Conectar via SSH para clonar o banco
        $ssh = new SSH2(env('WHM_IP'));

        // Caminho da chave privada (ex: storage_path('keys/id_rsa'))
        $keyPath = storage_path('keys/id_rsa');

        // Carrega a chave privada
        $key = PublicKeyLoader::loadPrivateKey(file_get_contents($keyPath), env('SSH_PASSPHRASE'));

        Log::info($key);

        // Autentica com chave privada
        if (!$ssh->login($this->cpanelUser, $key)) {
            throw new Exception('Falha na autenticação SSH com chave privada');
        }

        // Se chegou aqui, login foi bem-sucedido
        echo $ssh->exec('whoami');

        // Comando para clonar o banco de dados usando mysqldump
        $dumpCommand = "mysqldump -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$templateBanco} | mysql -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$database['name']}";

        $ssh->exec($dumpCommand);

        // Criar um usuário para o banco clonado
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_user", $this->cpanelUser, $this->cpanelPass, [
            "name" => $database['user'],
            "password" => $database['password']
        ]);

        // Conceder permissões ao novo usuário no banco clonado
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/set_privileges_on_database", $this->cpanelUser, $this->cpanelPass, [
            "user" => $database['user'],
            "database" => $database['name'],
            "privileges" => "ALL PRIVILEGES"
        ]);

        return response()->json([
            'message' => "Banco de dados {$database['name']} clonado com sucesso!",
            'usuario' => $database['user'],
            'password' => $database['password']
        ]);
    }


    /**
     * Cria um banco de dados e um usuário no cPanel.
     *
     * @param string $banco Nome do banco de dados
     * @return array Informações do banco criado
     */
    private function makeTable($banco)
    {
        // Gera nome do usuário com base no banco e um identificador único
        $usuario = $banco . "_usr";

        // Gera uma senha segura aleatória
        $senha = Str::random(12);

        // 1. Criar Banco de Dados
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_database", $this->cpanelUser, $this->cpanelPass, [
            "name" => $banco
        ]);

        // 2. Criar Usuário do Banco
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_user", $this->cpanelUser, $this->cpanelPass, [
            "name" => $usuario,
            "password" => $senha
        ]);

        // 3. Associar Usuário ao Banco com Permissões
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/set_privileges_on_database", $this->cpanelUser, $this->cpanelPass, [
            "user" => $usuario,
            "database" => $banco,
            "privileges" => "ALL PRIVILEGES"
        ]);
    }

    /**
     * Faz requisições HTTP para a API do cPanel usando Guzzle.
     *
     * @param string $method Método HTTP (GET, POST, etc.)
     * @param string $url URL da API do cPanel
     * @param string $user Usuário do cPanel
     * @param string $pass Senha do cPanel
     * @param array|null $data Parâmetros da requisição
     * @return array Resposta da API
     */
    private function guzzle($method, $url, $user, $pass, $data = null)
    {
        $client = new Guzzle();

        $options = [
            'auth' => [$user, $pass],
        ];

        if ($data !== null) {
            $options['query'] = $data;
        }

        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }

}
