<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Group $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    public function index()
    {
        // Obtém dados
        $groups = $this->repository->all();
        
        // Retorna a página
        return view('pages.groups.index')->with([
            'groups' => $groups,
        ]);
    }

    public function create()
    {   
        // Obtém dados dos Setores ativos
        $resources = Resource::where('status', true)->get();        

        // Retorna a página
        return view('pages.groups.create')->with([
            'resources' => $resources,
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

        if (isset($data['resources'])) {
            $created->resources()->sync($data['resources']);
        }
            // Retorna a página
            return redirect()
                    ->route('groups.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function edit($id)
    {
        // Obtém dados dos Setores ativos
        $resources = Resource::where('status', true)->get();       

        // Obtém dados
        $groups = $this->repository->find($id);

        // Verifica se existe
        if(!$groups) return redirect()->back();

        // Retorna a página
        return view('pages.groups.edit')->with([
            'groups' => $groups,
            'resources' => $resources
        ]);

    }

    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$groups = $this->repository->find($id)) return redirect()->back();

        // Armazena o nome antigo
        $oldName = $groups->name;

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $groups->update($data);

        if (isset($data['resources'])) {
            $groups->resources()->sync($data['resources']);
        }

        // Retorna a página
        return redirect()
        ->route('groups.index')
        ->with('message', 'Setor <b>'. $oldName . '</b> atualizado para <b>'. $groups->name .'</b> com sucesso.');
        
    }

    public function destroy($id)
    {

        // Obtém dados
        $groups = $this->repository->find($id);

        // Atualiza status
        if($groups->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('groups.index')
            ->with('message', 'Setor <b>'. $groups->name . '</b> '. $message .' com sucesso.');

    }
}
