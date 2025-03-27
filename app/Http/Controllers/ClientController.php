<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Module;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    protected $request;
    private $repository;
    private $cpanelMiCore;

    public function __construct(Request $request, Client $content)
    {

        $this->request = $request;
        $this->repository = $content;
        $this->cpanelMiCore = new CpanelController();

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtém dados
        $contents = $this->repository->orderBy('id', 'DESC')->get();

        // Retorna a página
        return view('pages.clients.index')->with([
            'contents' => $contents,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Retorna a página
        return view('pages.clients.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = Auth::id();

        // Gera um domínio permitido
        $data['domain'] = verifyIfAllow($data['name']);

        // Gera um nome de tabela permitido
        $data['table'] = str_replace('-', '_', $data['domain']);

        // Insere prefixo do miCore
        $data['table'] = 'micorecom_' . $data['table'];
        
        // Gera senha
        $data['password'] = Str::random(12);

        // Gera token para API
        $data['token'] = hash('sha256', $data['name'] . microtime(true));

        // Gera nome curto
        $data['user']['short_name'] = generateShortName($data['user']['name']);

        // Adiciona o sufixo dos domínios Core
        $data['domain'] = $data['domain'] . '.micore.com.br';

        // Insere no banco de dados
        $created = $this->repository->create($data);

         // Gera dado do banco de dados
        $database = [
            'name' => $data['table'],
            'password' => $data['password']
        ];

        // Gera subdomínio, banco de dados e usuário no Cpanel miCore.com.br
        $this->cpanelMiCore->make($data['domain'], $database, $data['user']);

        // Salva logo
        if(isset($data['fileLogo'])) $this->saveLogo($created, $data['fileLogo']);

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'Cliente <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtém dados dos Modulos ativos
        $modules = Module::where('status', true)->get();

        // Obtém dados do Cliente
        $client = $this->repository->find($id);

        // Realiza consulta
        $responseApi = $this->guzzle('get', 'sistema/permissoes', $client);

        // Reposta da API
        $apiError = false;
        
        // Se ocorreu um erro
        if (isset($responseApi['error']) && $responseApi['error'] == true) {
            $apiError = true;
        }

        // Inicia Array
        $allowFeatures = [];

        // Se foi tudo bem sucedido
        if(!$apiError){
            
            // Transforma em uma coleção
            $responseApi = $responseApi['permissions'];
    
            // Separa variáveis
            foreach ($responseApi as $value) {
                $allowFeatures[$value['name']] = $value['status'];
            }

        }

        // Obtém pacotes
        $packages = Package::where('status', true)->get();

        // Retorna a página
        return view('pages.clients.show')->with([
            'client'        => $client,
            'modules'       => $modules,
            'packages'      => $packages,
            'allowFeatures' => $allowFeatures,
            'apiError'      => $apiError,
            'responseApi'   => $responseApi,
        ]);

    }

    /**
     * Realiza uma solicitação Guzzle com autenticação Bearer
     *
     * @param string $method Método HTTP (get, post, etc)
     * @param string $url URL para a solicitação
     * @param object $client Objeto cliente contendo informações do cliente
     * @param array|null $data Dados opcionais para incluir na requisição
     * @return array Resposta da API
     */
    public function guzzle($method, $url, $client, $data = null)
    {
        try {
            // Instancia o Guzzle
            $guzzle = new Guzzle();

            // Inicializa os parâmetros da requisição
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('CENTRAL_TOKEN'),
                ]
            ];

            // Se houver dados, adiciona ao corpo da requisição
            if ($data !== null) {
                $options['json'] = $data;
            }

            // Realiza a solicitação
            $response = $guzzle->$method("http://$client->domain/api/$url", $options);

            // Obtém o corpo da resposta
            $response = $response->getBody()->getContents();

            // Decodifica o JSON
            $response = json_decode($response, true);

            // Retorna a resposta
            return $response;


        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Obtém dados
        $content = $this->repository->find($id);

        // Verifica se existe
        if(!$content) return redirect()->back();

        // Retorna a página
        return view('pages.clients.edit')->with([
            'content' => $content
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Verifica se existe
        if(!$content = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $content->update($data);

        // Salva logo
        if(isset($data['fileLogo'])) $this->saveLogo($content, $data['fileLogo']);

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'Cliente <b>'. $request->name . '</b> atualizado com sucesso.');

    }

    /**
     * Salva a logo do cliente, caso enviada.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $logo
     * @param  \App\Models\Client  $client
     * @param  string  $filename
     * @return void
     */
    public function saveLogo($client, $logo = null, $filename = 'logo.png')
    {
        if ($logo && $logo->isValid()) {
            $logo->storeAs("clientes/{$client->id}", $filename, 'public');
            $client->logo = true;
            $client->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // Obtém dados
        $content = $this->repository->find($id);

        // Atualiza status
        if($content->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'Cliente <b>'. $content->name . '</b> '. $message .' com sucesso.');

    }

}
