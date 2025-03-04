<?php

namespace App\Services;
use App\Models\Client;
use App\Models\ClientModule;
use App\Models\ClientPurchase;
use App\Models\ClientPurchaseItem;
use App\Models\ClientSubscription;
use App\Models\Package;

class PackageService
{
    public function assignNewPackage($client, $newPackage)
    {

        // Verifica se não esta atualizando para o mesmo pacote
        if($client->package_id == $newPackage->id){
            return 'O usuário já esta com o pacote "' . $newPackage->name . '" em sua conta.';
        }

        // Obtém o pacote atual e o novo
        $currentPackage = $client->package;

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
        if ($currentPackage && !$currentPackage->free) {
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

        return 'Pacote "' . $newPackage->name . '" adicionado com sucesso.';
        
    }
}