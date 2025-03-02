<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPurchase;
use App\Models\ClientPurchaseItem;
use App\Models\ClientSubscription;
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
        $package = $this->repository->find($id);
        $modules = Module::where('status', true)->get(); 

        // Verifica se existe
        if(!$package) return redirect()->back();

        // Retorna a página
        return view('pages.packages.edit')->with([
            'package' => $package,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Verifica se existe
        if(!$package = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        $oldName = $package->name;

        // Autor
        $data['value'] = toDecimal($data['value']);

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $package->update($data);

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
        $package = $this->repository->find($id);

        // Atualiza status
        if($package->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('packages.index')
            ->with('message', 'Pacote <b>'. $package->name . '</b> '. $message .' com sucesso.');

    }

    

    public function assign($id, Request $request)
    {

        // Obtém dados
        $data = $request->all();

        // Obtém cliente
        $client = Client::find($id);

        // Obtém dados
        $package = $this->repository->find($data['package_id']);

        // Obtém módulos
        $modules = $package->modules;

        // Cria registro de "Compra"
        $lastPurchase = ClientPurchase::create([
            'client_id'     => $client->id,
            'purchase_date' => now(),
            'total_value'   => 0.00,
            'method'        => 'Manual',
        ]);

        // Adiciona os módulos a compra
        foreach ($modules as $module) {
            ClientPurchaseItem::create([
                'purchase_id' => $lastPurchase->id,
                'item_type'   => 'Módulo',
                'item_name'   => $module->name,
                'item_key'    => $module->id,
                'quantity'    => 1,
                'item_value'  => $module->value,
            ]);
        }

        // Registra data da inscrição
        ClientSubscription::create([
            'client_id'  => $id,
            'package_id' => $data['package_id'],
            'start_date' => now(),
            'end_date'   => now()->addDays(30),
        ]);

        // Atualiza o pacote do cliente
        $client->package_id = $data['package_id'];
        $client->save();
        
        // Retorna a página
        return redirect()
            ->route('clients.show', $client->id)
            ->with('message', 'Pacote <b>'. $package->name . ' adicionado com sucesso.');

    }
}
