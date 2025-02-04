<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;


class CpanelController extends Controller
{
    protected $request;
    private $client;

    public function __construct(Request $request, Client $client)
    {
        $this->request = $request;
        $this->client = $client;
    }

    public function make()
    {
        // Gera um subdomínio aleatório
        $subdominio = 123;
        $rootDomain = "central.sulink.com.br";

        // Cria o subdomínio
        // $this->makeSubdomain($subdominio, $rootDomain);

        // Criar banco de dados e usuário
        $database = $this->makeTable($subdominio);

        return response()->json([
            // 'message' => 'Subdomínio e banco criados com sucesso!',
            'database' => $database,
            // 'subdominio' => "http://{$subdominio}.{$rootDomain}"
        ]);
    }

    private function makeSubdomain($subdominio, $rootDomain)
    {
        $documentRoot = "/home/centralofsystem/{$subdominio}";

        // Credenciais do cPanel via .env
        $cpanelUser = env('CPANEL_USER');
        $cpanelPass = env('CPANEL_PASS');
        $cpanelUrl  = "https://central.sulink.com.br:2083";

        // Faz a requisição para criar o subdomínio
        return $this->guzzle('GET', "{$cpanelUrl}/json-api/cpanel", $cpanelUser, $cpanelPass, [
            "cpanel_jsonapi_version" => 2,
            "cpanel_jsonapi_module" => "SubDomain",
            "cpanel_jsonapi_func" => "addsubdomain",
            "domain" => $subdominio,
            "rootdomain" => $rootDomain,
            "dir" => $documentRoot
        ]);
    }

    private function makeTable()
    {
        // Credenciais do cPanel via .env
        $cpanelUser = env('CPANEL_USER');
        $cpanelPass = env('CPANEL_PASS');
        $cpanelUrl  = "https://central.sulink.com.br:2083";

        // Gera nomes de banco, usuário e senha
        $banco = "central_" . Str::random(5);
        $usuario = "usr_" . Str::random(5);
        $senha = Str::random(12);

        // 1. Criar um novo usuário no cPanel
        $this->guzzle('GET', "{$cpanelUrl}/json-api/cpanel", $cpanelUser, $cpanelPass, [
            "cpanel_jsonapi_version" => 2,
            "cpanel_jsonapi_module" => "Passwd",
            "cpanel_jsonapi_func" => "passwd",
            "user" => $usuario,
            "password" => $senha
        ]);

        // 2. Criar Banco de Dados
        $this->guzzle('GET', "{$cpanelUrl}/json-api/cpanel", $cpanelUser, $cpanelPass, [
            "cpanel_jsonapi_version" => 2,
            "cpanel_jsonapi_module" => "Mysql",
            "cpanel_jsonapi_func" => "adddb",
            "db" => $banco
        ]);

        // 3. Criar Usuário do Banco
        $this->guzzle('GET', "{$cpanelUrl}/json-api/cpanel", $cpanelUser, $cpanelPass, [
            "cpanel_jsonapi_version" => 2,
            "cpanel_jsonapi_module" => "Mysql",
            "cpanel_jsonapi_func" => "adduser",
            "user" => $usuario,
            "password" => $senha
        ]);

        // 4. Associar Usuário ao Banco
        $this->guzzle('GET', "{$cpanelUrl}/json-api/cpanel", $cpanelUser, $cpanelPass, [
            "cpanel_jsonapi_version" => 2,
            "cpanel_jsonapi_module" => "Mysql",
            "cpanel_jsonapi_func" => "adduserdb",
            "user" => $usuario,
            "database" => $banco,
            "privileges" => "ALL"
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