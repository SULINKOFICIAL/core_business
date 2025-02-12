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
    private $client;
    private $cpanelPrefix;
    private $cpanelUrl;
    private $cpanelUser;
    private $cpanelPass;

    public function __construct(Request $request, Client $client)
    {
        $this->request = $request;
        $this->client = $client;

        // Centraliza configurações
        $this->cpanelPrefix = "centralofsystem_";
        $this->cpanelUrl = "https://micore.com.br:2083";
        $this->cpanelUser = env('CPANEL_USER');
        $this->cpanelPass = env('CPANEL_PASS');
    }

    public function make()
    {
        // Gera um subdomínio
        $subdominio = 'core' . 123;
        // $subdominio = 'core_' . Str::random(8);

        // Clona o repositório no subdomínio criado
        return $this->cloneRepository($subdominio);

        // Cria o subdomínio
        // $this->makeSubdomain($subdominio);

        // Gera nomes de banco, usuário e senha com o prefixo correto
        // $banco = $this->cpanelPrefix . Str::random(5);
        // $usuario = $this->cpanelPrefix . "usr_" . Str::random(5);
        // $senha = Str::random(12);

        // Criar banco de dados e usuário
        // $database = $this->makeTable($banco, $usuario, $senha);

        return response()->json([
            'message' => 'Subdomínio e banco criados com sucesso!',
            // 'database' => $database,
            'subdominio' => "http://{$subdominio}.micore.com.br"
        ]);
    }

    private function cloneRepository($subdominio)
    {
        $repoUrl = 'https://github.com/SULINKOFICIAL/coresulink.git';
        $path = "/home/micorecom/{$subdominio}";

        $ssh = new SSH2('micore.com.br');
        if (!$ssh->login('micorecom', 'V4Hr2u$Y5wJe')) {
            throw new Exception('Falha na autenticação SSH');
        }

        // Comando para clonar o repositório
        $command = "git clone {$repoUrl} {$path}";

        // Executando o comando
        $output = $ssh->exec($command);

        return $output;
    }

    private function createTestFileCpanel()
    {
        
        $path = "/home/micorecom";
        $content = "Arquivo de teste criado via API em " . date('Y-m-d H:i:s');

        $response = $this->guzzle('POST', "{$this->cpanelUrl}/execute/Fileman/save_file_content", $this->cpanelUser, $this->cpanelPass, [
            "dir" => $path,
            "file" => "teste.txt", // Alterado de 'filename' para 'file'
            "content" => $content,
        ]);

        return $response;
    }

    private function copyDirectory($source, $destination)
    {
        // Chama a API Fileman para copiar o diretório
        $response = $this->guzzle('POST', "{$this->cpanelUrl}/execute/Fileman/copy_file", $this->cpanelUser, $this->cpanelPass, [
            'dir' => dirname($source),
            'file' => basename($source),
            'to_dir' => $destination
        ]);

        // Retorna a resposta da API
        return $response;
    }



    private function makeSubdomain($subdominio)
    {
        $documentRoot = "/home/micorecom/{$subdominio}";

        // Chamada da API UAPI para criar subdomínio
        return $response = $this->guzzle('GET', "{$this->cpanelUrl}/execute/SubDomain/addsubdomain", $this->cpanelUser, $this->cpanelPass, [
            "domain" => $subdominio,
            "rootdomain" => "micore.com.br",
            "dir" => $documentRoot
        ]);
    }

    private function makeTable($banco, $usuario, $senha)
    {
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

        return [
            'database' => $banco,
            'user' => $usuario,
            'password' => $senha
        ];
    }


    private function guzzle($method, $url, $user, $pass, $data = null)
    {
        $options = [
            'auth' => [$user, $pass],
        ];

        if ($data !== null) {
            $options['query'] = $data;
        }

        $response = $this->client->request($method, $url, $options);
        $responseBody = json_decode($response->getBody()->getContents(), true);
        dd($responseBody);

        return $responseBody;

    }
}
