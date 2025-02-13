<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
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
        $this->cpanelUrl = "https://micore.com.br:2083";
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
    public function make($domain, $table)
    {

        // 1. Cria o subdomínio
        $this->makeSubdomain($domain);

        // 2. Cria o banco de dados
        $this->makeTable($table);

        return response()->json([
            'message' => 'Subdomínio e banco criados com sucesso!',
            'subdominio' => "http://{$domain}.micore.com.br"
        ]);
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
