<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use mysqli;
use phpseclib3\Net\SSH2;

class CpanelController extends Controller
{
    protected $request;
    private $cpanelPrefix;
    private $cpanelUrl;
    private $cpanelUser;
    private $cpanelPass;

    public function __construct()
    {
        // Define as configurações do cPanel
        $this->cpanelPrefix = "centralofsystem_";
        $this->cpanelUrl = env('CPANEL_URL');
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
    public function make($domain, $table, $password)
    {
        $host = 'mysqli.micore.com.br';
        $db   = 'micorecom_teste';
        $user = 'micorecom_teste_usr';
        $pass = 'spaS2Ug2wkxn';

        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        } else {
            echo "Conectado com sucesso!";
        }



        // // 1. Cria o subdomínio
        // $this->makeSubdomain($domain);

        // // 2. Cria o banco de dados
        // $this->cloneDatabase($table, $password);

        // // 3. Adiciona registros únicos no cliente
        // $this->addTokenAndUser('micorecom_teste', 'micorecom_teste_usr', 'spaS2Ug2wkxn');

        // return response()->json([
        //     'message' => 'Subdomínio e banco clonado com sucesso!',
        //     'subdominio' => "http://{$domain}.micore.com.br"
        // ]);
    }
   
    private function addTokenAndUser($dbName, $dbUser, $dbPassword)
    {
        // Conectar ao banco recém-criado
        $this->conectarAoBancoDoCliente($dbName, $dbUser, $dbPassword);

        try {
            DB::connection('mysql_cliente')->getPdo();
            echo "Conexão bem-sucedida!";
        } catch (\Exception $e) {
            die("Erro ao conectar: " . $e->getMessage());
        }

        dd(123);

        // Gerar senha hashada e token de API
        $senhaPadrao = bcrypt('senha123'); // Defina a senha inicial do usuário
        $apiToken = Str::random(60);

        // Inserir usuário padrão
        DB::connection('mysql_cliente')->table('users')->insert([
            'name'             => 'Admin',
            'password'         => $senhaPadrao,
            'show_store_id'    => null,
            'bio'              => 'Administrador padrão',
            'full_name'        => 'Administrador',
            'email'            => 'admin@' . $dbName . '.com',
            'id_master'        => 1,
            'role'             => 'admin',
            'dashboard_active' => 1,
            'api_token'        => $apiToken,
            'sac_attendence'   => 1,
            'sac_order'        => 1,
            'visible_website'  => 1,
            'status'           => 'active',
            'created_by'       => 'system',
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


    private function conectarAoBancoDoCliente($dbName, $dbUser, $dbPassword)
    {
        
        // dd($dbName, $dbUser, $dbPassword);

        config([
            'database.connections.mysql_cliente' => [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', 'localhost'),
                'database'  => $dbName,
                'username'  => $dbUser,
                'password'  => $dbPassword,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            ]
        ]);

        DB::purge('mysql_cliente'); // Limpa conexões antigas
        DB::reconnect('mysql_cliente'); // Reconecta ao banco correto
        
    }



    /**
     * Cria um subdomínio via API do cPanel.
     *
     * @param string $domain Nome do subdomínio a ser criado
     * @return array Resposta da API do cPanel
     */
    private function makeSubdomain($domain)
    {
        $documentRoot = "/home/micorecom/public_html";

        $this->guzzle('GET', "{$this->cpanelUrl}/execute/SubDomain/addsubdomain", $this->cpanelUser, $this->cpanelPass, [
            "domain" => $domain,
            "rootdomain" => "micore.com.br",
            "dir" => $documentRoot
        ]);
    }

    /**
     * Clona um banco de dados existente (micorecom_template) para um novo banco.
     *
     * @param string $novoBanco Nome do novo banco de dados
     * @return void
     * @throws Exception Se a clonagem falhar
     */
    private function cloneDatabase($novoBanco, $password)
    {
        // Banco modelo
        $templateBanco = 'micorecom_template';

        // Criar o novo banco de dados
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_database", $this->cpanelUser, $this->cpanelPass, [
            "name" => $novoBanco
        ]);

        // Conectar via SSH para clonar o banco
        $ssh = new SSH2('micore.com.br');
        if (!$ssh->login($this->cpanelUser, $this->cpanelPass)) {
            throw new Exception('Falha na autenticação SSH');
        }

        // Comando para clonar o banco de dados usando mysqldump
        $dumpCommand = "mysqldump -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$templateBanco} | mysql -u {$this->cpanelUser} -p'{$this->cpanelPass}' {$novoBanco}";

        $ssh->exec($dumpCommand);

        // Criar um usuário para o banco clonado
        $usuario = $novoBanco . "_usr";

        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/create_user", $this->cpanelUser, $this->cpanelPass, [
            "name" => $usuario,
            "password" => $password
        ]);

        // Conceder permissões ao novo usuário no banco clonado
        $this->guzzle('GET', "{$this->cpanelUrl}/execute/Mysql/set_privileges_on_database", $this->cpanelUser, $this->cpanelPass, [
            "user" => $usuario,
            "database" => $novoBanco,
            "privileges" => "ALL PRIVILEGES"
        ]);

        return response()->json([
            'message' => "Banco de dados {$novoBanco} clonado com sucesso!",
            'usuario' => $usuario,
            'password' => $password
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
        $client = new Client(); // Instanciando a classe Client diretamente

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

        $ssh = new SSH2('micore.com.br');
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
