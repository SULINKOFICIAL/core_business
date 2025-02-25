<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use mysqli;
use phpseclib3\Net\SSH2;

class CpanelController extends Controller
{
    protected $request;
    private $cpanelHost;
    private $cpanelUrl;
    private $cpanelUser;
    private $cpanelPass;

    public function __construct()
    {
        // Define as configurações do cPanel
        $this->cpanelHost = env('CPANEL_HOST');
        $this->cpanelUrl  = env('CPANEL_URL');
        $this->cpanelUser = env('CPANEL_USER');
        $this->cpanelPass = env('CPANEL_PASS');
    }

    /**
     * Cria um subdomínio e um banco de dados associado.
     *
     * @param string $domain Nome do subdomínio
     * @param string $table Nome da tabela (não utilizada atualmente)
     * @return \Illuminate\Http\JsonResponse
     */
    public function make($domain, $datatable, $user)
    {

        // Registra tempo
        Log::info("Criando subdomínio: " . $domain);

        // // 1. Cria o subdomínio
        $this->makeSubdomain($domain);
        
        // Registra tempo
        Log::info("Clonando banco template para : " . $datatable['name']);

        // // 2. Cria o banco de dados
        $this->cloneDatabase($datatable);
        
        // Registra tempo
        Log::info("Inserindo usuário e token no banco : " . $datatable['name']);

        // Separador
        Log::info("======================================");
        Log::info("================ FIM =================");
        Log::info("======================================");

        // // 3. Adiciona registros únicos no cliente
        $this->addTokenAndUser($datatable, $user);

        // Registra tempo
        Log::info("Finalizou a inserção dos usuário e token no banco : " . $datatable['name']);

        // Retorna a página
        return response()->json([
            'url' => "https://" . $domain,
            'message' => 'Conta criada com sucesso',
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
            'password' => $client->password,
        ];

        // Envia a solicitação para criar o subdomínio
        $response = $this->cloneDatabase($database, $client->name);

        // Retorna a resposta da API
        return $response;

    }


    /**
     * Adiciona token e usuário no banco de dados do cliente.
     */
    private function addTokenAndUser($datatable, $user)
    {
        // Conectar ao banco recém-criado
        $this->connectDatabase($datatable);

        // Gerar senha hashada e token de API
        $userPassword = Hash::make($user['password']);
        $apiToken = Str::random(60);


        return 3333;

        // Inserir usuário padrão
        DB::connection('mysql_cliente')->table('users')->insert([
            'name'       => $user['short_name'],
            'password'   => $userPassword,
            'full_name'  => $user['name'],
            'email'      => $user['email'],
            'role'       => 1,
            'created_by' => 1,
        ]);

        // Inserir o token na tabela `configs_api`
        DB::connection('mysql_cliente')->table('configs_api')->insert([
            'plataform'    => 'micore',
            'option_name'  => 'api_token',
            'option_value' => $apiToken,
            'updated_by'   => 'system',
        ]);

        return $apiToken;
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
                'host'      => $this->cpanelHost,
                'database'  => $datatable['name'],
                'username'  => $datatable['name'] . '_usr',
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

        $documentRoot = "/home/micorecom/core";

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
        
        // Obtém banco de dados
        $database = [
            'name' => $client->table,
            'password' => $client->password,
        ];

        // Envia a solicitação para criar o subdomínio
        $response = $this->cloneDatabase($database);

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
    private function cloneDatabase($database)
    {
        // Banco modelo
        $templateBanco = 'micorecom_template';

        // Criar o novo banco de dados
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_database", $this->cpanelUser, $this->cpanelPass, [
            "name" => $database['name']
        ]);

        // Conectar via SSH para clonar o banco
        $ssh = new SSH2(env('WHM_IP'));
        if (!$ssh->login($this->cpanelUser, $this->cpanelPass)) {
            throw new Exception('Falha na autenticação SSH');
        }

        // Comando para clonar o banco de dados usando mysqldump
        $dumpCommand = "mysqldump -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$templateBanco} | mysql -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$database['name']}";

        $ssh->exec($dumpCommand);

        // Criar um usuário para o banco clonado
        $usuario = $database['name'] . "_usr";

        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_user", $this->cpanelUser, $this->cpanelPass, [
            "name" => $usuario,
            "password" => $database['password']
        ]);

        // Conceder permissões ao novo usuário no banco clonado
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/set_privileges_on_database", $this->cpanelUser, $this->cpanelPass, [
            "user" => $usuario,
            "database" => $database['name'],
            "privileges" => "ALL PRIVILEGES"
        ]);

        return response()->json([
            'message' => "Banco de dados {$database['name']} clonado com sucesso!",
            'usuario' => $usuario,
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
        $client = new Guzzle(); // Instanciando a classe Client diretamente

        $options = [
            'auth' => [$user, $pass],
        ];

        if ($data !== null) {
            $options['query'] = $data;
        }

        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Clona o repositório Laravel para o subdomínio via SSH.
     *
     * @param string $domain Nome do subdomínio
     * @return string Saída do comando SSH
     * @throws Exception Se a autenticação SSH falhar
     */
    private function cloneRepository($domain)
    {
        $repoUrl = 'https://github.com/SULINKOFICIAL/coresulink.git';
        $path = "/home/micorecom/{$domain}";

        $ssh = new SSH2(env('WHM_IP'));
        if (!$ssh->login($this->cpanelUser, $this->cpanelPass)) {
            throw new Exception('Falha na autenticação SSH');
        }

        // Comando para clonar o repositório
        $command = "git clone {$repoUrl} {$path}";
        return $ssh->exec($command);
    }

    /**
     * Cria um arquivo de teste no servidor via API do cPanel.
     *
     * @return array Resposta da API do cPanel
     */
    private function createTestFileCpanel()
    {
        $path = "/home/micorecom";
        $content = "Arquivo de teste criado via API em " . date('Y-m-d H:i:s');

        return $this->guzzle('POST', "{$this->cpanelUrl}/execute/Fileman/save_file_content", $this->cpanelUser, $this->cpanelPass, [
            "dir" => $path,
            "file" => "teste.txt",
            "content" => $content,
        ]);
    }
}
