<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Module;
use App\Models\PackageModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Package $content)
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

        // Obtém pacotes
        $packages = $this->repository->all();

        // Retorna a página
        return view('pages.packages.index')->with([
            'packages' => $packages,
        ]);

    }

    public function create()
    {
        
        // Obtém módulos
        $modules = Module::where('status', true)->get(); 

        // Retorna a página
        return view('pages.packages.create')->with([
            'modules' => $modules,
        ]);

    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Autor
        $data['value'] = toDecimal($data['value']);

        // Autor
        $data['created_by'] = Auth::id();

        // Insere no banco de dados
        $created = $this->repository->create($data);

        // Insere módulos no pacote
        foreach ($data['modules'] as $moduleId) {
            PackageModule::create([
                'module_id'  => $moduleId,
                'package_id' => $created->id,
                'created_by' => Auth::id(),
            ]);
        }

        // Retorna a página
        return redirect()
                ->route('packages.index')
                ->with('message', 'Pacote <b>'. $created->name . '</b> adicionado com sucesso.');
                
    }

    public function edit($id)
    {
        // Obtém dados
        $packages = $this->repository->find($id);
        $modules = Module::where('status', true)->get(); 

        // Verifica se existe
        if(!$packages) return redirect()->back();

        // Retorna a página
        return view('pages.packages.edit')->with([
            'packages' => $packages,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Verifica se existe
        if(!$packages = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        $oldName = $packages->name;

        // Autor
        $data['value'] = toDecimal($data['value']);

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $packages->update($data);

        // Remove pacotes anteriores
        PackageModule::where('package_id', $id)->delete();

        // Insere módulos no pacote
        foreach ($data['modules'] as $moduleId) {
            PackageModule::create([
                'module_id'  => $moduleId,
                'package_id' => $id,
                'created_by' => Auth::id(),
            ]);
        }

        // Retorna a página
        return redirect()
            ->route('packages.edit', $id)
            ->with('message', 'Pacote <b>'. $oldName . '</b> atualizado para <b>'. $packages->name .'</b> com sucesso.');
    }

    public function destroy($id)
    {

        // Obtém dados
        $packages = $this->repository->find($id);

        // Atualiza status
        if($packages->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('packages.index')
            ->with('message', 'Pacote <b>'. $packages->name . '</b> '. $message .' com sucesso.');

    }
}
