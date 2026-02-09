<?php

namespace App\Services;

use App\Models\ClientModule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemConfiguration;
use App\Models\ClientSubscription;
use App\Models\Package;
use App\Models\Module;

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
            'currency'        => 'BRL',
        ]);

        // Adiciona pacote na compra
        OrderItem::create([
            'order_id'    => $order->id,
            'item_type'   => 'package',
            'action'      => 'Alteração',
            'item_code'   => (string) $newPackage->id,
            'item_name_snapshot' => $newPackage->name,
            'quantity'    => 1,
            'unit_price_snapshot' => $newPackage->value,
            'subtotal_amount' => $newPackage->value,
            // Legacy compatibility
            'type'        => 'Pacote',
            'item_name'   => $newPackage->name,
            'item_key'    => $newPackage->id,
            'item_value'  => $newPackage->value,
        ]);

        // Adiciona os novos módulos
        foreach ($newPackage->modules as $module) {
            OrderItem::create([
                'order_id'  => $order->id,
                'item_type' => 'module',
                'action'    => 'Adição',
                'item_code' => (string) $module->id,
                'item_name_snapshot' => $module->name,
                'quantity'  => 1,
                'unit_price_snapshot' => 0,
                'subtotal_amount' => 0,
                // Legacy compatibility
                'type'      => 'Módulo',
                'item_name' => $module->name,
                'item_key'  => $module->id,
                'item_value' => 0,
            ]);
        }

        // Adiciona crédito se necessário
        if ($credit > 0) {
            OrderItem::create([
                'order_id'    => $order->id,
                'item_type'   => 'credit',
                'action'      => 'Ajuste',
                'item_name_snapshot' => 'Crédito proporcional',
                'quantity'    => 1,
                'unit_price_snapshot' => -$credit,
                'subtotal_amount' => -$credit,
                // Legacy compatibility
                'type'        => 'Crédito',
                'item_name'   => 'Crédito proporcional',
                'item_value'  => -$credit,
            ]);
        }

        $totalAmount = (float) $order->items()->sum('subtotal_amount');
        $order->update([
            'total_amount' => $totalAmount,
            'pricing_snapshot' => [
                'package_id' => $newPackage->id,
                'previous_package_id' => $oldPackage,
                'items_count' => $order->items()->count(),
                'calculated_at' => now()->toDateTimeString(),
            ],
        ]);

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
            ]);

            // Extende a data da assinatura a partir da última
            $startDate = $currentSubscription->end_date;
            
        }

        // Verifique se a data final já passou
        if ($startDate->isPast()) $startDate = now();

        // Separa a data de encerramento
        $endDate = $startDate->clone();

        // Criar nova assinatura
        ClientSubscription::create([
            'client_id'  => $client->id,
            'package_id' => $package->id,
            'order_id'   => $order->id,
            'start_date' => $startDate,
            'end_date'   => $endDate->addDays($package->duration_days),
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

    /**
     * Cria um pedido em rascunho com base nos módulos e configurações.
     *
     * @param  \\App\\Models\\Client $client
     * @param  array $modulesInput Array de itens {id, config}
     * @param  string $currency
     * @return \\App\\Models\\Order
     */
    public function createDraftOrderFromModules($client, array $modulesInput, string $currency = 'BRL', ?int $orderId = null): Order
    {
        /**
         * Monta o rascunho do pedido com base nos módulos enviados,
         * preservando o mesmo pedido quando for uma atualização.
         */
        $moduleIds = [];
        $configsById = [];

        foreach ($modulesInput as $item) {
            if (!is_array($item) || !isset($item['id'])) {
                // Garante o formato correto do payload
                continue;
            }

            // Converte para inteiro para evitar valores inválidos
            $moduleId = (int) $item['id'];
            if ($moduleId <= 0) {
                // Impede ids não numéricos ou negativos
                continue;
            }

            // Acumula ids e configs por módulo
            $moduleIds[] = $moduleId;
            $configsById[$moduleId] = isset($item['config']) && is_array($item['config']) ? $item['config'] : [];
        }

        // Remove duplicados
        $moduleIds = array_values(array_unique($moduleIds));
        if (empty($moduleIds)) {
            // Não permite pedido sem módulos
            return $orderId
                ? (Order::where('id', $orderId)->where('client_id', $client->id)->where('status', 'draft')->first() ?? Order::create([
                    'client_id' => $client->id,
                    'status' => 'draft',
                    'currency' => $currency,
                    'pricing_snapshot' => [
                        'source' => 'central',
                        'modules_requested' => [],
                        'calculated_at' => now()->toDateTimeString(),
                    ],
                ]))
                : Order::create([
                    'client_id' => $client->id,
                    'status' => 'draft',
                    'currency' => $currency,
                    'pricing_snapshot' => [
                        'source' => 'central',
                        'modules_requested' => [],
                        'calculated_at' => now()->toDateTimeString(),
                    ],
                ]);
        }

        // Carrega módulos válidos com seus tiers
        $modules = Module::with('pricingTiers')->whereIn('id', $moduleIds)->where('status', true)->get();
        if ($modules->isEmpty()) {
            // Bloqueia módulos inexistentes ou inativos
            $order = Order::create([
                'client_id' => $client->id,
                'status' => 'draft',
                'currency' => $currency,
                'pricing_snapshot' => [
                    'source' => 'central',
                    'modules_requested' => [],
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);
            return $order;
        }

        $order = null;
        if ($orderId) {
            // Reusa o rascunho existente quando informado
            $order = Order::where('id', $orderId)
                ->where('client_id', $client->id)
                ->where('status', 'draft')
                ->first();
        }

        if (!$order) {
            // Cria novo rascunho caso não exista
            $order = Order::create([
                'client_id' => $client->id,
                'status' => 'draft',
                'currency' => $currency,
                'pricing_snapshot' => [
                    'source' => 'central',
                    'modules_requested' => $moduleIds,
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);
        } else {
            // Remove itens/configs para reconstruir o rascunho
            $order->items()->delete();
            $order->update([
                // Atualiza moeda e snapshot
                'currency' => $currency,
                'pricing_snapshot' => [
                    'source' => 'central',
                    'modules_requested' => $moduleIds,
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        // Inicia total do pedido
        $total = 0.0;

        foreach ($modules as $module) {
            // Configuração dinâmica do módulo (quando existir)
            $config = $configsById[$module->id] ?? [];

            // Valor base do módulo
            $unitPrice = 0.0;
            $pricingModelSnapshot = [
                'type' => $module->pricing_type,
            ];

            if ($module->pricing_type === 'usage') {
                // Extrai o uso informado pelo cliente
                $usage = null;
                if (isset($config['usage']) && is_numeric($config['usage'])) {
                    $usage = (float) $config['usage'];
                } elseif (isset($config['volume']) && is_numeric($config['volume'])) {
                    $usage = (float) $config['volume'];
                }

                // Ordena as faixas de preço por limite de uso
                $tiers = $module->pricingTiers->sortBy('usage_limit')->values();
                if ($tiers->isEmpty()) {
                    // Bloqueia módulos por uso sem faixas configuradas
                    continue;
                }

                if ($usage === null) {
                    // Marca como pendente quando o uso não foi informado
                    $pricingModelSnapshot['pending_usage'] = true;
                    $unitPrice = 0.0;
                } else {
                    // Encontra o tier correspondente ao uso informado
                    $matchedTier = null;
                    foreach ($tiers as $tier) {
                        if ($usage <= (float) $tier->usage_limit) {
                            $matchedTier = $tier;
                            break;
                        }
                    }
                    if (!$matchedTier) {
                        $matchedTier = $tiers->last();
                    }

                    // Aplica preço da faixa selecionada
                    $unitPrice = (float) $matchedTier->price;
                    // Salva detalhes do cálculo no snapshot
                    $pricingModelSnapshot['usage'] = $usage;
                    $pricingModelSnapshot['tier_limit'] = (float) $matchedTier->usage_limit;
                    $pricingModelSnapshot['tier_price'] = (float) $matchedTier->price;
                }
            } else {
                // Preço fixo para módulos sem cobrança por uso
                $unitPrice = (float) $module->value;
            }

            // Cria item do pedido com snapshot imutável
            $item = OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'module',
                'action' => 'Adição',
                'item_code' => (string) $module->id,
                'item_name_snapshot' => $module->name,
                'item_reference_type' => Module::class,
                'item_reference_id' => $module->id,
                'quantity' => 1,
                'unit_price_snapshot' => $unitPrice,
                'subtotal_amount' => $unitPrice,
                'pricing_model_snapshot' => $pricingModelSnapshot,
                // Legacy compatibility
                'type' => 'Módulo',
                'item_name' => $module->name,
                'item_key' => $module->id,
                'item_value' => $unitPrice,
            ]);

            foreach ($config as $key => $value) {
                // Persiste cada configuração do módulo
                OrderItemConfiguration::create([
                    'order_item_id' => $item->id,
                    'key' => (string) $key,
                    'value' => is_scalar($value) ? (string) $value : json_encode($value),
                    'value_type' => gettype($value),
                    'derived_pricing_effect' => $pricingModelSnapshot,
                ]);
            }

            // Soma ao total do pedido
            $total += $unitPrice;
        }

        // Atualiza o total do pedido com base no cupom, se existir
        $this->recalculateOrderTotals($order, $total);

        // Retorna o rascunho atualizado
        return $order;
    }

    /**
     * Recalcula o total do pedido considerando o cupom aplicado.
     */
    private function recalculateOrderTotals(Order $order, ?float $subtotal = null): void
    {

        // Soma o subtotal atual caso não seja informado
        $itemsSubtotal = $subtotal ?? (float) $order->items()->sum('subtotal_amount');
        // Calcula desconto do cupom quando existir
        $couponDiscount = $this->calculateCouponDiscount($order, $itemsSubtotal);
        // Calcula o total final do pedido
        $totalAmount = $itemsSubtotal - $couponDiscount;
        if ($totalAmount < 0) {
            $totalAmount = 0.0;
        }

        $order->update([
            'total_amount' => $totalAmount,
            'coupon_discount_amount' => $couponDiscount,
        ]);

    }

    /**
     * Calcula o desconto do cupom aplicado no pedido.
     */
    private function calculateCouponDiscount(Order $order, float $subtotal): float
    {

        if (!$order->coupon_id || !$order->coupon_type_snapshot) {
            return 0.0;
        }

        $type = $order->coupon_type_snapshot;
        $value = (float) ($order->coupon_value_snapshot ?? 0);

        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($type === 'percent') {
            $discount = $subtotal * ($value / 100);
        } elseif ($type === 'fixed') {
            $discount = $value;
        } elseif ($type === 'trial') {
            $discount = $subtotal;
        } else {
            $discount = 0.0;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return $discount;

    }

}
