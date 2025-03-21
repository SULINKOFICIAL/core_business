<?php

namespace App\Services;
use App\Models\Client;
use App\Models\ClientModule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientSubscription;
use App\Models\Package;

class PackageService
{
    public function createPaymentIntent($client, $newPackage, $method = 'Manual', $gateway = null)
    {

        // Verifica se não esta atualizando para o mesmo pacote
        if ($client->package_id == $newPackage->id) {
            return [
                'status' => 'Falha', 
                'message' => 'O cliente já esta com esse plano atribuido',
            ];
        }

        // Se tiver alguma inteção de compra cancela e gera uma nova
        Order::where('status', 'Pendente')->update(['status' => 'Cancelado']);

        $currentPackage = $client->package;
        $credit = 0;

        if ($currentPackage && !$currentPackage->free) {
            $daysInMonth = now()->daysInMonth;
            $daysUsed = now()->day;
            $daysRemaining = $daysInMonth - $daysUsed;
            $dailyRate = $currentPackage->value / $daysInMonth;
            $credit = $dailyRate * $daysRemaining;
        }

        // Se for uma troca
        if($currentPackage){
            $type = 'Pacote Trocado';
            $oldPackage = $currentPackage->id;
        } else {
            $type = 'Pacote Atribuido';
            $oldPackage = null;
        }

        // Criar intenção de compra
        $order = Order::create([
            'client_id'       => $client->id,
            'order_date'   => now(),
            'type'            => $type,
            'key_id'          => $newPackage->id,
            'previous_key_id' => $oldPackage,
            'method'          => $method,
            'gateway_id'      => $gateway,
            'status'          => 'Pendente',
        ]);

        // Adiciona pacote na compra
        OrderItem::create([
            'order_id' => $order->id,
            'type'        => 'Pacote',
            'action'      => 'Alteração',
            'item_name'   => $newPackage->name,
            'item_key'    => $newPackage->id,
            'quantity'    => 1,
            'item_value'  => $newPackage->value,
        ]);

        // Adiciona os novos módulos
        foreach ($newPackage->modules as $module) {
            OrderItem::create([
                'order_id' => $order->id,
                'type' => 'Módulo',
                'action' => 'Adição',
                'item_name' => $module->name,
                'item_key' => $module->id,
                'quantity' => 1,
                'item_value' => 0,
            ]);
        }

        // Adiciona crédito se necessário
        if ($credit > 0) {
            OrderItem::create([
                'order_id' => $order->id,
                'type'        => 'Crédito',
                'action'      => 'Ajuste',
                'item_name'   => 'Crédito proporcional',
                'quantity'    => 1,
                'item_value'  => -$credit,
            ]);
        }

        return [
            'status' => 'Sucesso', 
            'order' => $order
        ];
    }

    public function confirmPackageChange($order)
    {
        if ($order->status !== 'Pago') {
            return 'Pagamento ainda não confirmado.';
        }

        $client = $order->client;
        $newPackage = Package::find($order->key_id);

        if (!$newPackage) {
            return 'Pacote não encontrado.';
        }

        // Cancela assinatura atual
        $currentSubscription = $client->lastSubscription();
        if ($currentSubscription) {
            $currentSubscription->update([
                'status'   => 'Cancelado',
                'end_date' => now(),
            ]);
        }

        // Remove módulos antigos
        ClientModule::where('client_id', $client->id)->delete();

        // Adiciona novos módulos
        foreach ($newPackage->modules as $module) {
            ClientModule::create([
                'client_id'  => $client->id,
                'module_id'  => $module->id,
            ]);
        }

        // Criar nova assinatura
        ClientSubscription::create([
            'client_id'  => $client->id,
            'package_id' => $newPackage->id,
            'order_id'   => $order->id,
            'start_date' => now(),
            'end_date'   => now()->addDays($newPackage->duration_days),
            'status'     => 'Ativa',
        ]);

        // Atualizar cliente com novo pacote
        $client->update([
            'package_id' => $newPackage->id,
        ]);

        return 'Pacote "' . $newPackage->name . '" ativado com sucesso.';
    }

}