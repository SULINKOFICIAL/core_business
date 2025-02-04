<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

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
        $this->cpanelUrl = "https://central.sulink.com.br:2083";
        $this->cpanelUser = env('CPANEL_USER');
        $this->cpanelPass = env('CPANEL_PASS');
    }

    public function make()
    {
        // Gera um subdomínio
        $subdominio = 123;
        // $subdominio = Str::random(8);

        // Cria o subdomínio
        $this->makeSubdomain($subdominio);

        // Gera nomes de banco, usuário e senha com o prefixo correto
        // $banco = $this->cpanelPrefix . Str::random(5);
        // $usuario = $this->cpanelPrefix . "usr_" . Str::random(5);
        // $senha = Str::random(12);

        // Criar banco de dados e usuário
        // $database = $this->makeTable($banco, $usuario, $senha);

        return response()->json([
            'message' => 'Subdomínio e banco criados com sucesso!',
            // 'database' => $database,
            'subdominio' => "http://{$subdominio}.central.sulink.com.br"
        ]);
    }

    private function makeSubdomain($subdominio)
    {
        $documentRoot = "/home/centralofsystem/{$subdominio}";

        // Chamada da API UAPI para criar subdomínio
        return $response = $this->guzzle('GET', "{$this->cpanelUrl}/execute/SubDomain/addsubdomain", $this->cpanelUser, $this->cpanelPass, [
            "domain" => $subdominio,
            "rootdomain" => "central.sulink.com.br",
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

        return json_decode($response->getBody()->getContents(), true);
    }
}
