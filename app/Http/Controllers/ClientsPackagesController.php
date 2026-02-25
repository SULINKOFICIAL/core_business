<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\ClientPackageItem;
use App\Models\Module;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ClientsPackagesController extends Controller
{
    /**
     * Cria um pacote em rascunho com base nos módulos desejados.
     */
    public function update(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Extrai cliente
        $client = $data['client'];

        // Cria ou atualiza o pacote em rascunho com os módulos enviados
        $package = $this->getPackageInProgress($client);

        // Realiza ação desejada
        $action = match ($data['action']) {
            'change_module' => $this->toggleModule($package, $data['value']),
            default => null,
        };

        // Retorna resposta
        return response()->json([
            'message' => $action['message'],
            'package' => $package,
        ]);
    }

    /**
     * Adiciona ou remove o módulo
     */
    private function toggleModule($package, $moduleId)
    {
        // Busca dados do módulo
        $module = Module::find($moduleId);

        // Verifica se esse pedido já tem esse item
        $existingItem = ClientPackageItem::where('package_id', $package->id)
            ->where('item_id', $moduleId)
            ->first();

        // Se o módulo já existe, remove
        if ($existingItem) {
            $existingItem->delete();

            // Recalcula os totais
            $this->recalculatePackageTotal($package);

            return [
                'message' => 'Módulo removido com sucesso.',
                'action' => 'removed',
            ];
        }

        // Cria item de módulo no pedido
        ClientPackageItem::create([
            'package_id' => $package->id,
            'item_id' => $module->id,
        ]);

        // Recalcula os totais
        $this->recalculatePackageTotal($package);

        return [
            'message' => 'Módulo adicionado com sucesso.',
            'action' => 'added',
        ];
    }

    /**
     * Ajusta o preço do pacote
     */
    public function recalculatePackageTotal(ClientPackage $package)
    {
        // Soma o subtotal atual caso não seja informado
        $itemsSubtotal = $package->modules()->sum('value');

        // Calcula o total final do pedido
        $totalAmount = $itemsSubtotal;
        if ($totalAmount < 0) {
            $totalAmount = 0.0;
        }

        $package->update([
            'value' => $totalAmount,
        ]);
    }
}
