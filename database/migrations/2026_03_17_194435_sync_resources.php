<?php

use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\ClientPackageItem;
use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTransaction;
use App\Models\Resource;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Services\GuzzleService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtem todos os clientes
        $client = Client::find(3);

        $guzzleService = new GuzzleService();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Module::truncate();
        ModuleCategory::truncate();
        Resource::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Realiza solicitação
        $categories = $guzzleService->request('post', 'sistema/permissoes-recursos', $client, []);

        // Decodifica a resposta
        $categories = json_decode($categories['data'], true);

        /**
         * Define o status de todos os registros como 0 antes da verificação.
         * Em seguida, verifica se a permissão recebida das rotas do Core 
         * corresponde a algum registro existente na tabela de Recursos.
         * 
         * Se houver correspondência, o status será atualizado para 1.
         * Se o status permanecer 0, significa que o nome da permissão recebida 
         * não corresponde a nenhum registro existente em Recursos.
         */
        Resource::where('status', true)->update([
            'status' => 0,
        ]);

        /**
         * Faz looping pelas categorias
         */
        foreach ($categories as $category) {

            /**
             * Verifica se veio com pacote ou sem
             */
            if($category['name'] != 'Sem Pacote') {

                /**
                 * Cria a categoria no sistema
                 */
                $moduleCategory = ModuleCategory::updateOrCreate([
                    'name' => $category['name'],
                ], [
                    'status' => true,
                    'created_by' => 1
                ]);

                $categoryId = $moduleCategory->id;

            } else {
                $categoryId = null;
            }
            
            // Faz looping entre modulos
            foreach ($category['modules'] as $key => $module) {

                /**
                 * Cria os módulos
                 */
                $modelModule = Module::updateOrCreate([
                    'slug' => $key,
                    'module_category_id' => $categoryId,
                ], [
                    'name' => $module['name'],
                    'description' => $module['description'],
                    'created_by' => 1
                ]);

                // Faz looping entre os recursos
                foreach ($module['resources'] as $resource) {

                    /**
                     * Busca um registro onde o campo 'name' seja igual a $permission.
                     * 
                     * Se o registro existir, atualiza o campo 'status' para true.
                     * Se o registro não existir, cria um novo com 'name' = $permission 
                     * e 'status' = true.
                     */
                    Resource::updateOrCreate(
                        [
                            'name' => $resource,
                            'module_id' => $modelModule->id,
                        ],
                        [
                            'status' => true,
                            'created_by' => 1
                        ]
                    );
                }

            }
        }

        // Obtem todos os clientes
        $clients = Client::all();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Order::truncate();
        OrderItem::truncate();
        OrderTransaction::truncate();
        SubscriptionCycle::truncate();
        Subscription::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Faz looping em cada cliente
        foreach ($clients as $client) {

            $package = ClientPackage::create([
                'client_id' => $client->id,
                'name' => 'MIGRAÇÃO',
                'price' => 0,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Pega todos os IDs de módulo
            $moduleIds = Module::pluck('id')->toArray();

            // Monta os dados para insert em massa
            $packageItems = array_map(function($moduleId) use ($package) {
                return [
                    'package_id' => $package->id,
                    'item_id' => $moduleId,
                    'created_at' => now(),
                ];
            }, $moduleIds);

            // Inserção em massa
            ClientPackageItem::insert($packageItems);

            // Cria um pedido
            $order = Order::create([
                'client_id' => $client->id,
                'package_id' => $package->id,
                'total_amount' => 0,
                'status' => 'Liberado',
                'type' => 'MIGRAÇÃO',
                'current_step' => 'Pagamento',
                'created_at' => now(),
            ]);

            $subscription = Subscription::create([
                'pagarme_subscription_id' => '1',
                'pagarme_card_id' => '1',
                'interval' => 'year',
                'payment_method' => 'liberado',
                'currency' => 'BRL',
                'installments' => 1,
                'status' => 'paid',
                'created_at' => now(),
            ]);

            SubscriptionCycle::create([
                'subscription_id' => $subscription->id,
                'pagarme_cycle_id' => '1',
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'status' => 'billed',
                'cycle' => 1,
                'billing_at' => now(),
                'next_billing_at' => now()->addYear(),
                'created_at' => now(),
            ]);

            OrderTransaction::create([
                'order_id' => $order->id,
                'subscription_id' => $subscription->id,
                'pagarme_transaction_id' => '1',
                'amount' => 0,
                'status' => 'paid',
                'method' => 'liberado',
                'currency' => 'BRL',
                'created_at' => now(),
            ]);

        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
