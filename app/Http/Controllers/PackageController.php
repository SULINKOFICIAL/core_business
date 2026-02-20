<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientModule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Module;
use App\Models\PackageModule;
use App\Services\OrderService;
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
        $this->orderService = new OrderService;

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
    public function assign(Request $request, $id)
    {

       

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
            $order = Order::create([
                'client_id'  => $id,
                'type'       => 'Pacote alterado',
                'key_id'     => $client->package_id,
                'method'     => 'Manual',
                'status'     => false,
            ]);

            // Registra o valor anterior do pacote
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'package',
                'action' => 'Sem alteração',
                'item_code' => (string) $client->package->id,
                'item_name_snapshot' => "{$client->package->name} ({$client->users_limit} usuários)",
                'quantity' => 1,
                'unit_price_snapshot' => $client->current_value,
                'subtotal_amount' => $client->current_value,
                // Legacy compatibility
                'type' => 'Pacote',
                'item_name' => "{$client->package->name} ({$client->users_limit} usuários)",
                'item_key' => $client->package->id,
                'item_value' => $client->current_value,
            ]);

            // **Registra os Itens da Compra**
            if ($limitUsers) {
                
                // Calcula o valor do novo limite de usuários
                $priceLimitUsers = ($data['users_limit'] - 3) * 29.90;

                // Cria item 
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'users',
                    'action' => 'Alteração',
                    'item_code' => (string) $data['users_limit'],
                    'item_name_snapshot' => 'Quantidade alterada',
                    'quantity' => 1,
                    'unit_price_snapshot' => $priceLimitUsers,
                    'subtotal_amount' => $priceLimitUsers,
                    // Legacy compatibility
                    'type' => 'Usuários',
                    'item_name' => 'Quantidade alterada',
                    'item_key' => $data['users_limit'],
                    'item_value' => $priceLimitUsers,
                    'start_date' => now(),
                ]);

                // Atualiza no cliente
                $client->users_limit = $data['users_limit'];

            }

            // Adiciona módulos como Upgrade
            foreach ($modulesAdded as $moduleId) {
                $module = Module::find($moduleId);
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'module',
                    'action' => 'Upgrade',
                    'item_code' => (string) $module->id,
                    'item_name_snapshot' => $module->name,
                    'quantity' => 1,
                    'unit_price_snapshot' => $module->value,
                    'subtotal_amount' => $module->value,
                    // Legacy compatibility
                    'type' => 'Modulo',
                    'item_name' => $module->name,
                    'item_key' => $module->id,
                    'item_value' => $module->value,
                ]);
            }

            // Remove módulos como Downgrade
            foreach ($modulesRemoved as $moduleId) {
                $module = Module::find($moduleId);
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'module',
                    'action' => 'Downgrade',
                    'item_code' => (string) $module->id,
                    'item_name_snapshot' => $module->name,
                    'quantity' => 1,
                    'unit_price_snapshot' => -$module->value,
                    'subtotal_amount' => -$module->value,
                    // Legacy compatibility
                    'type' => 'Modulo',
                    'item_name' => $module->name,
                    'item_key' => $module->id,
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
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'credit',
                'action' => 'Ajuste',
                'item_name_snapshot' => 'Crédito',
                'quantity' => 1,
                'unit_price_snapshot' => -$credit,
                'subtotal_amount' => -$credit,
                // Legacy compatibility
                'type' => 'Crédito',
                'item_name' => 'Crédito',
                'item_value' => -$credit,
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

}
