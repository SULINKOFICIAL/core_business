<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientSubscription;
use App\Models\ClientSubscriptionItem;
use App\Models\Gateway;
use App\Models\Module;
use App\Models\ModulePricingTier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemConfiguration;
use App\Models\OrderTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure gateways
        $gatewayErede = Gateway::firstOrCreate(['name' => 'eRede'], ['status' => true]);
        $gatewayManual = Gateway::firstOrCreate(['name' => 'Manual'], ['status' => true]);

        // Ensure modules with at least one usage-based module
        $modules = Module::where('status', true)->get();
        if ($modules->isEmpty()) {
            $modules = collect([
                Module::create([
                    'name' => 'Gestão',
                    'description' => 'Módulo de gestão',
                    'value' => 29.90,
                    'pricing_type' => 'fixed',
                    'status' => 1,
                    'created_by' => 1,
                ]),
                Module::create([
                    'name' => 'Atendimento',
                    'description' => 'Módulo de atendimento',
                    'value' => 19.90,
                    'pricing_type' => 'fixed',
                    'status' => 1,
                    'created_by' => 1,
                ]),
                Module::create([
                    'name' => 'Vendas',
                    'description' => 'Módulo de vendas',
                    'value' => 49.90,
                    'pricing_type' => 'usage',
                    'usage_label' => 'Pedidos/mês',
                    'status' => 1,
                    'created_by' => 1,
                ]),
            ]);
        }

        // Configure a usage-based module with tiers
        $usageModule = $modules->firstWhere('pricing_type', 'usage');
        if ($usageModule) {
            if (ModulePricingTier::where('module_id', $usageModule->id)->count() === 0) {
                ModulePricingTier::create([
                    'module_id' => $usageModule->id,
                    'usage_limit' => 1000,
                    'price' => 59.90,
                ]);
                ModulePricingTier::create([
                    'module_id' => $usageModule->id,
                    'usage_limit' => 5000,
                    'price' => 99.90,
                ]);
                ModulePricingTier::create([
                    'module_id' => $usageModule->id,
                    'usage_limit' => 10000,
                    'price' => 149.90,
                ]);
            }
        }

        // Ensure clients
        $clients = Client::all();
        if ($clients->isEmpty()) {
            $clients = collect([
                Client::create([
                    'name' => 'Cliente Exemplo 1',
                    'email' => 'cliente1@example.com',
                    'company' => 'Empresa 1',
                    'token' => Str::random(40),
                    'status' => 1,
                    'created_by' => 1,
                ]),
                Client::create([
                    'name' => 'Cliente Exemplo 2',
                    'email' => 'cliente2@example.com',
                    'company' => 'Empresa 2',
                    'token' => Str::random(40),
                    'status' => 1,
                    'created_by' => 1,
                ]),
            ]);
        }

        foreach ($clients as $client) {
            // Draft order
            $draftOrder = Order::create([
                'client_id' => $client->id,
                'status' => 'draft',
                'currency' => 'BRL',
                'description' => 'Intenção de compra em rascunho',
                'pricing_snapshot' => [
                    'source' => 'seed',
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);

            $this->seedOrderItems($draftOrder, $modules, 'Adição', false);

            // Pending order
            $pendingOrder = Order::create([
                'client_id' => $client->id,
                'status' => 'Pendente',
                'currency' => 'BRL',
                'description' => 'Pedido pendente para pagamento',
                'pricing_snapshot' => [
                    'source' => 'seed',
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);

            $this->seedOrderItems($pendingOrder, $modules, 'Adição', true);

            // Paid order
            $paidOrder = Order::create([
                'client_id' => $client->id,
                'status' => 'Pago',
                'currency' => 'BRL',
                'description' => 'Pedido pago',
                'paid_at' => now()->subDays(rand(1, 10)),
                'pricing_snapshot' => [
                    'source' => 'seed',
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]);

            $this->seedOrderItems($paidOrder, $modules, 'Adição', true);

            $total = $paidOrder->items()->sum('subtotal_amount');
            $paidOrder->update(['total_amount' => $total]);

            $transaction = OrderTransaction::create([
                'order_id' => $paidOrder->id,
                'gateway_id' => $gatewayErede->id,
                'status' => 'paid',
                'amount' => $total,
                'currency' => 'BRL',
                'method' => 'Gateway',
                'raw_response_snapshot' => [
                    'returnCode' => '00',
                    'message' => 'Pago (seed)',
                ],
                'paid_at' => now()->subDays(rand(0, 5)),
            ]);

            // Create subscription from paid order
            $subscription = ClientSubscription::create([
                'client_id' => $client->id,
                'order_id' => $paidOrder->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'current_period_start' => now()->startOfDay(),
                'current_period_end' => now()->addMonth()->startOfDay(),
                'next_billing_at' => now()->addMonth()->startOfDay(),
                'start_date' => now()->startOfDay(),
                'end_date' => now()->addMonth()->startOfDay(),
            ]);

            foreach ($paidOrder->items as $item) {
                if ($item->item_type !== 'module') {
                    continue;
                }

                ClientSubscriptionItem::create([
                    'client_subscription_id' => $subscription->id,
                    'module_id' => $item->item_reference_id,
                    'module_code' => $item->item_code,
                    'status' => 'active',
                    'current_config_snapshot' => $item->pricing_model_snapshot,
                    'current_price_snapshot' => [
                        'unit' => $item->unit_price_snapshot,
                        'subtotal' => $item->subtotal_amount,
                    ],
                ]);
            }
        }
    }

    private function seedOrderItems(Order $order, $modules, string $action, bool $includeConfigs): void
    {
        $total = 0.0;
        $items = $modules->shuffle()->take(min(3, $modules->count()));

        foreach ($items as $module) {
            $unitPrice = (float) $module->value;
            $pricingModel = [
                'type' => $module->pricing_type,
            ];

            if ($module->pricing_type === 'usage') {
                $usage = rand(500, 9000);
                $tier = ModulePricingTier::where('module_id', $module->id)
                    ->where('usage_limit', '>=', $usage)
                    ->orderBy('usage_limit')
                    ->first();

                if (!$tier) {
                    $tier = ModulePricingTier::where('module_id', $module->id)
                        ->orderBy('usage_limit', 'DESC')
                        ->first();
                }

                if ($tier) {
                    $unitPrice = (float) $tier->price;
                    $pricingModel['usage'] = $usage;
                    $pricingModel['tier_limit'] = (float) $tier->usage_limit;
                    $pricingModel['tier_price'] = (float) $tier->price;
                }
            }

            $item = OrderItem::create([
                'order_id' => $order->id,
                'item_type' => 'module',
                'action' => $action,
                'item_code' => (string) $module->id,
                'item_name_snapshot' => $module->name,
                'item_reference_type' => Module::class,
                'item_reference_id' => $module->id,
                'quantity' => 1,
                'unit_price_snapshot' => $unitPrice,
                'subtotal_amount' => $unitPrice,
                'pricing_model_snapshot' => $pricingModel,
                // Legacy
                'type' => 'Módulo',
                'item_name' => $module->name,
                'item_key' => $module->id,
                'item_value' => $unitPrice,
            ]);

            if ($includeConfigs && $module->pricing_type === 'usage') {
                OrderItemConfiguration::create([
                    'order_item_id' => $item->id,
                    'key' => 'usage',
                    'value' => (string) ($pricingModel['usage'] ?? 0),
                    'value_type' => 'integer',
                    'derived_pricing_effect' => $pricingModel,
                ]);
            }

            $total += $unitPrice;
        }

        $order->update([
            'total_amount' => $total,
        ]);
    }
}
