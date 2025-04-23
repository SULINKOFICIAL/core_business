<?php

namespace App\Services;
use App\Models\Client;
use App\Models\ClientModule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientSubscription;
use App\Models\Package;
use Carbon\Carbon;

class OrderService
{
    public function createOrder($client, $newPackage)
    {

        // Verifica se não esta atualizando para o mesmo pacote
        if ($client->package_id == $newPackage->id) {

            // Obtém pedido de renovação do cliente
            $orderRenovation = $client->orders()->where('type', 'Renovação')->where('status', 'pendente')->first();

            // Retorna ordem ou falha
            if($orderRenovation){
                return [
                    'status' => 'Pedido de renovação encontrado.', 
                    'order' => $orderRenovation
                ];
            } else {
                return [
                    'status' => 'Falha', 
                    'message' => 'O cliente já esta com esse plano atribuido e não possui renovação pendentes.',
                ];
            }
            
        }

        // Se já existir um pedido em andamento referente aquele item/produtos.
        $existsOrder = Order::where('client_id', $client->id)
                            ->where('key_id', $newPackage->id)
                            ->where('status', 'Pendente')
                            ->first();

        // Retorna o pedido em andamento
        if($existsOrder){
            return [
                'status' => 'Pedido já foi gerado.', 
                'order' => $existsOrder
            ];
        }

        // Obtém pacote atual
        $currentPackage = $client->package;

        // Inicia variável que vai calcular o crédito do cliente
        $credit = 0;

        /**
         * Verifica se o cliente estava em um pacote
         * gratuito, se estiver não calcula créditos.
         */
        if ($currentPackage && !$currentPackage->free) {
            $daysInMonth   = now()->daysInMonth;
            $daysUsed      = now()->day;
            $daysRemaining = $daysInMonth - $daysUsed;
            $dailyRate     = $currentPackage->value / $daysInMonth;
            $credit        = $dailyRate * $daysRemaining;
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
            'type'            => $type,
            'key_id'          => $newPackage->id,
            'previous_key_id' => $oldPackage,
            'status'          => 'Pendente',
        ]);

        // Adiciona pacote na compra
        OrderItem::create([
            'order_id'    => $order->id,
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
                'order_id'  => $order->id,
                'type'      => 'Módulo',
                'action'    => 'Adição',
                'item_name' => $module->name,
                'item_key'  => $module->id,
                'quantity'  => 1,
                'item_value' => 0,
            ]);
        }

        // Adiciona crédito se necessário
        if ($credit > 0) {
            OrderItem::create([
                'order_id'    => $order->id,
                'type'        => 'Crédito',
                'action'      => 'Ajuste',
                'item_name'   => 'Crédito proporcional',
                'quantity'    => 1,
                'item_value'  => -$credit,
            ]);
        }

        return [
            'status' => 'Pedido Gerado', 
            'order' => $order
        ];
    }

    public function confirmPaymentOrder($order)
    {

        // Verifica se o pagamento já foi processado
        if ($order->status === 'Pago') return 'Esse Pagamento já foi aprovado.';

        // Busca o cliente
        $client = $order->client;

        // Busca o pacote a ser renovado
        $package = Package::find($order->key_id);

        // Obtém a última assinatura
        $currentSubscription = $client->lastSubscription();

        /**
         * Caso não seja uma renovação, indica que o usuário esta 
         * trocando o pacote dele ou esta sendo atribuido um novo.
         */
        if($order->type != 'Renovação'){

            // Cancela assinatura atual
            if ($currentSubscription) {
                $currentSubscription->update([
                    'status'   => 'Cancelado',
                    'end_date' => now(),
                ]);
            }

            // Remove módulos antigos
            ClientModule::where('client_id', $client->id)->delete();

            // Adiciona novos módulos
            foreach ($package->modules as $module) {
                ClientModule::create([
                    'client_id'  => $client->id,
                    'module_id'  => $module->id,
                ]);
            }

            // Define a data de inicio da nova assinatura para hoje
            $startDate = now();

        } else {

            // Muda o status da assinatura atual para renovada
            $currentSubscription->update([
                'status'   => 'Renovada',
                'end_date' => now(),
            ]);

            // Extende a data da assinatura a partir da última
            $startDate = $currentSubscription->end_date;

            // Verifique se a data final já passou
            if ($startDate->isPast()) $startDate = now();
            
        }

        // Criar nova assinatura
        ClientSubscription::create([
            'client_id'  => $client->id,
            'package_id' => $package->id,
            'order_id'   => $order->id,
            'start_date' => $startDate,
            'end_date'   => $startDate->addDays($package->duration_days),
            'status'     => 'Ativo',
        ]);

        // Atualizar cliente com novo pacote
        $client->update([
            'package_id' => $package->id,
        ]);

        // Atualiza o pedido
        $order->status = 'Pago';
        $order->paid_at = now();
        $order->save();

        return 'Pacote "' . $package->name . '" ativado com sucesso.';
        
    }

}