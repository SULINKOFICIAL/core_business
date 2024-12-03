<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Resource $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    public function index()
    {
        // Obtém dados
        $resources = $this->repository->all();
        
        // Retorna a página
        return view('pages.resources.index')->with([
            'resources' => $resources,
        ]);
    }

    public function create()
    {   

        // Retorna a página
        return view('pages.resources.create')->with([
        ]);

    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = Auth::id();

        // Insere no banco de dados
        $created = $this->repository->create($data);

            // Retorna a página
            return redirect()
                    ->route('resources.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function edit($id)
    {

            // Obtém dados
            $resources = $this->repository->find($id);

            // Verifica se existe
            if(!$resources) return redirect()->back();
    
            // Retorna a página
            return view('pages.resources.edit')->with([
                'resources' => $resources
            ]);

    }

    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$resources = $this->repository->find($id)) return redirect()->back();

        // Armazena o nome antigo
        $oldName = $resources->name;

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $resources->update($data);

        // Retorna a página
        return redirect()
        ->route('resources.index')
        ->with('message', 'Setor <b>'. $oldName . '</b> atualizado para <b>'. $resources->name .'</b> com sucesso.');
        
    }

    public function destroy($id)
    {

        // Obtém dados
        $resources = $this->repository->find($id);

        // Atualiza status
        if($resources->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('resources.index')
            ->with('message', 'Setor <b>'. $resources->name . '</b> '. $message .' com sucesso.');

    }
}
