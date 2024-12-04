<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectorController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Sector $content)
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
        $sectors = Sector::all();

        // Retorna a página
        return view('pages.sectors.index')->with([
            'sectors' => $sectors,
        ]);
    }

    public function create()
    {   
        // Obtém dados dos Grupos ativos
        $groups = Group::where('status', true)->get(); 

        // Retorna a página
        return view('pages.sectors.create')->with([
            'groups' => $groups,
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

        if (isset($data['groups'])) {
            $created->groups()->sync($data['groups']);
        }

            // Retorna a página
            return redirect()
                    ->route('sectors.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function edit($id)
    {
        // Obtém dados dos Grupos ativos
        $groups = Group::where('status', true)->get();        

        // Obtém dados
        $sectors = $this->repository->find($id);

        // Verifica se existe
        if(!$sectors) return redirect()->back();

        // Retorna a página
        return view('pages.sectors.edit')->with([
            'sectors' => $sectors,
            'groups' => $groups
        ]);

    }
    
    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$sectors = $this->repository->find($id)) return redirect()->back();

        // Armazena o nome antigo
        $oldName = $sectors->name;

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $sectors->update($data);

        if (isset($data['groups'])) {
            $sectors->groups()->sync($data['groups']);
        }

        // Retorna a página
        return redirect()
        ->route('sectors.index')
        ->with('message', 'Setor <b>'. $oldName . '</b> atualizado para <b>'. $sectors->name .'</b> com sucesso.');
        
    }

    public function destroy($id)
    {

        // Obtém dados
        $sectors = $this->repository->find($id);

        // Atualiza status
        if($sectors->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('sectors.index')
            ->with('message', 'Setor <b>'. $sectors->name . '</b> '. $message .' com sucesso.');

    }

}


