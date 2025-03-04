<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientModule;
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
            ->with('message', 'Pacote <b>'. $oldName . '</b> atualizado para <b>'. $package->name .'</b> com sucesso.');
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

    
    /**
     * Atribui um pacote a um cliente sem pacotes.
     */
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
            'type'          => 'Pacote Atribuido',
            'key_id'        => $package->id,
            'purchase_date' => now(),
            'method'        => 'Manual',
        ]);

        // Adiciona módulo a compra do cliente
        ClientPurchaseItem::create([
            'purchase_id' => $lastPurchase->id,
            'type'        => 'Pacote',
            'action'      => 'Adição',
            'item_name'   => $package->name,
            'item_key'    => $package->id,
            'quantity'    => 1,
            'item_value'  => $package->value,
        ]);

        // Adiciona os módulos a compra e ao cliente
        foreach ($modules as $module) {

            // Adiciona módulo a compra do cliente
            ClientPurchaseItem::create([
                'purchase_id' => $lastPurchase->id,
                'type'        => 'Módulo',
                'action'      => 'Adição',
                'item_name'   => $module->name,
                'item_key'    => $module->id,
                'quantity'    => 1,
                'item_value'  => 0,
            ]);

            // Libera módulo para o cliente
            ClientModule::create([
                'client_id' => $client->id,
                'module_id' => $module->id,
            ]);
        }

        // Registra data da inscrição
        ClientSubscription::create([
            'client_id'     => $id,
            'package_id'    => $data['package_id'],
            'purschase_id'  => $lastPurchase->id,
            'start_date'    => now(),
            'end_date'      => now()->addDays(30),
        ]);

        // Atualiza o pacote do cliente
        $client->package_id    = $data['package_id'];
        $client->save();
        
        // Retorna a página
        return redirect()
            ->route('clients.show', $client->id)
            ->with('message', 'Pacote <b>'. $package->name . ' adicionado com sucesso.');

    }


    /**
     * Personaliza um pacote para um cliente.
     */
    public function upgrade(Request $request, $id)
    {
        // Inicia configurações
        $limitUsers = null;
        $moduleChange = null;

        // Obtém o cliente
        $client = Client::find($id);

        // Obtém dados do formulário
        $data = $request->all();

        // Obtém os módulos atuais do cliente
        $actualModules = $client->modules->pluck('id')->toArray();

        // Obtém os novos módulos do request
        $newModules = isset($data['modules']) ? $data['modules'] : [];

        // Verifica se houve mudança de módulos (upgrade ou downgrade)
        $modulesAdded = array_diff($newModules, $actualModules); // Novos módulos adicionados
        $modulesRemoved = array_diff($actualModules, $newModules); // Módulos removidos

        if (!empty($modulesAdded) && empty($modulesRemoved)) {
            $moduleChange = 'Upgrade Módulos';
        } elseif (!empty($modulesRemoved) && empty($modulesAdded)) {
            $moduleChange = 'Downgrade Módulos';
        } elseif (!empty($modulesAdded) && !empty($modulesRemoved)) {
            $moduleChange = 'Mudança de Módulos';
        }

        // Verifica se houve mudança no limite de usuários
        if ($data['users_limit'] < $client->users_limit) {
            $limitUsers = 'Downgrade';
        } elseif ($data['users_limit'] > $client->users_limit) {
            $limitUsers = 'Upgrade';
        }

        // Remove módulos antigos
        ClientModule::where('client_id', $id)->delete();

        // Adiciona novos módulos ao cliente
        foreach ($newModules as $moduleId) {
            ClientModule::create([
                'client_id' => $id,
                'module_id' => $moduleId,
            ]);
        }

        // Recarrega os módulos após a atualização
        $client->load('modules');

        // **Criação da Compra**
        if ($limitUsers || $moduleChange) {

            // Cria a compra
            $purchase = ClientPurchase::create([
                'client_id' => $id,
                'purchase_date' => now(),
                'type'          => 'Pacote alterado',
                'key_id'        => $client->package_id,
                'method'        => 'Manual',
                'status'        => false,
            ]);

            // Registra o valor anterior do pacote
            ClientPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'type' => 'Pacote',
                'action' => 'Sem alteração',
                'item_name' => "{$client->package->name} ({$client->users_limit} usuários)",
                'item_key' => $client->package->id,
                'quantity' => 1,
                'item_value' => $client->current_value,
            ]);

            // **Registra os Itens da Compra**
            if ($limitUsers) {
                
                // Calcula o valor do novo limite de usuários
                $priceLimitUsers = ($data['users_limit'] - 3) * 29.90;

                // Cria item 
                ClientPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'type' => 'Usuários',
                    'action' => 'Alteração',
                    'item_name' => 'Quantidade alterada',
                    'item_key' => $data['users_limit'],
                    'quantity' => 1,
                    'item_value' => $priceLimitUsers,
                    'start_date' => now(),
                ]);

                // Atualiza no cliente
                $client->users_limit = $data['users_limit'];

            }

            // Adiciona módulos como Upgrade
            foreach ($modulesAdded as $moduleId) {
                $module = Module::find($moduleId);
                ClientPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'type' => 'Modulo',
                    'action' => 'Upgrade',
                    'item_name' => $module->name,
                    'item_key' => $module->id,
                    'quantity' => 1,
                    'item_value' => $module->value,
                ]);
            }

            // Remove módulos como Downgrade
            foreach ($modulesRemoved as $moduleId) {
                $module = Module::find($moduleId);
                ClientPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'type' => 'Modulo',
                    'action' => 'Downgrade',
                    'item_name' => $module->name,
                    'item_key' => $module->id,
                    'quantity' => 1,
                    'item_value' => -$module->value,
                ]);
            }

            // Aqui somamos o valor de todos os módulos ativos para o cliente
            $totalPrice = $client->modules->sum('value') + 29.90;

            // Obtém o valor diário do plano atual
            $daysInMonth = now()->daysInMonth;
            $dailyRate = $client->current_value / $daysInMonth;

            // Calcula os dias restantes
            $daysUsed = now()->day; // Quantidade de dias já utilizados no mês
            $daysRemaining = $daysInMonth - $daysUsed;

            // Calcula o crédito proporcional
            $credit = $dailyRate * $daysRemaining;

            // Adiciona crédito
            ClientPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'type'        => 'Crédito',
                'action'      => 'Ajuste',
                'item_name'   => 'Crédito',
                'quantity'    => 1,
                'item_value'  => -$credit,
            ]);
            
            // O valor dos usuários deve ser calculado com base no limite de usuários
            if ($client->users_limit > 3) {
                $totalPrice += ($client->users_limit - 3) * 29.90;
            }
            
            // Atualiza no cliente
            $client->current_value = $totalPrice;
            $client->save();

        }

        // Retorna a página
        return redirect()
            ->route('clients.show', $id)
            ->with('message', 'Configurações da conta do cliente atualizadas com sucesso.');
    }



    /**
     * Troca o pacote do cliente.
     */
    public function new(Request $request, $id)
    {
        // Obtém os dados da requisição
        $data = $request->all();

        // Obtém cliente
        $client = Client::findOrFail($id);

        // Obtém o pacote atual e o novo
        $currentPackage = $client->package;
        $newPackage = Package::findOrFail($data['package_id']);

        // Obtém a assinatura ativa atual
        $currentSubscription = $client->lastSubscription();

        // Se houver uma assinatura ativa, finaliza ela
        if ($currentSubscription) {
            $currentSubscription->update([
                'status' => 'Cancelado',
                'end_date' => now(),
            ]);
        }

        // Calcula crédito se necessário
        $credit = 0;
        if (!$currentPackage->free) {
            $daysInMonth = now()->daysInMonth;
            $daysUsed = now()->day;
            $daysRemaining = $daysInMonth - $daysUsed;
            $dailyRate = $currentPackage->value / $daysInMonth;
            $credit = $dailyRate * $daysRemaining;
        }

        // Criar nova compra
        $purchase = ClientPurchase::create([
            'client_id' => $client->id,
            'purchase_date' => now(),
            'type' => 'Pacote Trocado',
            'key_id' => $newPackage->id,
            'previous_key_id' => $currentPackage->id,
            'method' => 'Manual',
            'status' => true,
        ]);

        // Adiciona novo pacote na compra
        ClientPurchaseItem::create([
            'purchase_id' => $purchase->id,
            'type' => 'Pacote',
            'action' => 'Alteração',
            'item_name' => $newPackage->name,
            'item_key' => $newPackage->id,
            'quantity' => 1,
            'item_value' => $newPackage->value,
        ]);

        // Adiciona crédito na compra, se houver
        if ($credit > 0) {
            ClientPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'type' => 'Crédito',
                'action' => 'Ajuste',
                'item_name' => 'Crédito proporcional',
                'quantity' => 1,
                'item_value' => -$credit,
            ]);
        }

        // Remove os módulos antigos
        ClientModule::where('client_id', $client->id)->delete();

        // Adiciona os novos módulos
        foreach ($newPackage->modules as $module) {
            ClientPurchaseItem::create([
                'purchase_id' => $purchase->id,
                'type' => 'Módulo',
                'action' => 'Adição',
                'item_name' => $module->name,
                'item_key' => $module->id,
                'quantity' => 1,
                'item_value' => 0,
            ]);

            ClientModule::create([
                'client_id' => $client->id,
                'module_id' => $module->id,
            ]);
        }

        // Criar nova assinatura
        ClientSubscription::create([
            'client_id' => $client->id,
            'package_id' => $newPackage->id,
            'purschase_id' => $purchase->id,
            'start_date' => now(),
            'end_date' => now()->addDays($newPackage->duration_days),
            'status' => 'Ativa',
        ]);

        // Atualizar o cliente com o novo pacote
        $client->update([
            'package_id' => $newPackage->id,
        ]);

        return redirect()
            ->route('clients.show', $client->id)
            ->with('message', 'Pacote atualizado com sucesso.');
    }
}
