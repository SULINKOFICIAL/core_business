<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Client $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtém dados
        $contents = $this->repository->orderBy('name', 'ASC')->get();

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

        // Insere no banco de dados
        $created = $this->repository->create($data);

        // Salva logo
        $this->saveLogo($data['logo'], $created);

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

        // Obtém dados do Lead
        $contents = $this->repository->find($id);

        // Obtém módulos
        $modules = $this->modules();

        // Retorna a página
        return view('pages.clients.show')->with([
            'contents' => $contents,
            'modules' => $modules,
        ]);

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
        $this->saveLogo($data['fileLogo'], $content);

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
    public function saveLogo($logo, $client, $filename = 'logo.png')
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

    public function modules() {

        // Gera lista de módulos disponíveis no sistema
        $modules = [];

        // Módulo Financeiro
        $modules[] = [
            'nome' => 'Financeiro',
            'frase' => 'Gestão financeira',
            'recursos' => [
                'básico' => [
                    'Carteiras',
                    'Categorias',
                    'Fornecedores',
                ],
                'Contas a Pagar' => [
                    'Gerenciar Despesas',
                    'Gerar Relatórios',
                ],
                'Contas a Receber' => [
                    'Gerenciar Receitas',
                ],
            ],
        ];

        // Módulo Usuários
        $modules[] = [
            'nome' => 'Usuários',
            'frase' => 'Gerenciamento de pessoas',
            'recursos' => [
                'básico' => [
                    'Gerenciar',
                    'Permissões',
                    'Grupo de usuários',
                ],
            ],
        ];

        // Retorna pacotes
        return $modules;

    }
}
