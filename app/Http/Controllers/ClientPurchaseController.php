<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientModule;
use App\Models\ClientPurchase;
use App\Models\ClientPurchaseItem;
use App\Models\Module;
use Illuminate\Http\Request;

class ClientPurchaseController extends Controller
{ 
     /**
      * Store a newly created resource in storage.
      *
      * @param  \Illuminate\Http\Request  $request
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     public function store(Request $request, $id)
     {
          
          // Inicia configurações
          $limitUsers = null;
          $moduleChange = null;
          $totalValue = 29.90;

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

          // **Criação da Compra**
          if ($limitUsers || $moduleChange) {

               // Cria a compra
               $purchase = ClientPurchase::create([
                    'client_id' => $id,
                    'purchase_date' => now(),
                    'previous_value' => $client->current_value,
                    'total_value' => 0,
                    'method' => 1,
               ]);

               // **Registra os Itens da Compra**
               if ($limitUsers) {

                    // Calcula o valor do novo limite de usuários
                    $priceLimitUsers = ($data['users_limit'] - 3) * 29.90;

                    // Cria item 
                    ClientPurchaseItem::create([
                         'purchase_id' => $purchase->id,
                         'item_type' => 'Upgrade',
                         'item_name' => 'Usuários',
                         'quantity' => 1,
                         'item_value' => $priceLimitUsers,
                         'start_date' => now(),
                    ]);

                    // Atualiza no cliente
                    $client->users_limit = $data['users_limit'];

                    // Soma ao total
                    $totalValue += $priceLimitUsers;
               }

               // Adiciona módulos como Upgrade
               foreach ($modulesAdded as $moduleId) {
                    $module = Module::find($moduleId);
                    ClientPurchaseItem::create([
                         'purchase_id' => $purchase->id,
                         'item_type' => 'Upgrade',
                         'item_name' => $module->id,
                         'quantity' => 1,
                         'item_value' => $module->value,
                         'start_date' => now(),
                         'end_date' => now()->addYear(),
                    ]);
                    
                    // Soma ao total
                    $totalValue += $module->value;
               }

               // Remove módulos como Downgrade
               foreach ($modulesRemoved as $moduleId) {
                    $module = Module::find($moduleId);
                    ClientPurchaseItem::create([
                         'purchase_id' => $purchase->id,
                         'item_type' => 'Downgrade',
                         'item_name' => $module->id,
                         'quantity' => 1,
                         'item_value' => -$module->value, // Deduz o valor do total
                         'start_date' => now(),
                         'end_date' => now()->addYear(),
                    ]);
                    
                    // Subtrai do total
                    $totalValue -= $module->value;
               }

               // Atualiza no cliente
               $client->current_value = $totalValue;
               $client->save();

               // Atualiza o total da compra
               $purchase->update(['total_value' => $totalValue]);
               
          }

          // Retorna a página
          return redirect()
               ->route('clients.show', $id)
               ->with('message', 'Configurações da conta do cliente atualizadas com sucesso.');

     }


}
