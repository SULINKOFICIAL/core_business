<?php

namespace App\Services;

use App\Models\Module;

class ModuleService
{
    protected $guzzleService;

    public function __construct(GuzzleService $guzzleService)
    {
        $this->guzzleService = $guzzleService;
    }

    public function configureModules($tenant, array $moduleIds, $status = true)
    {
        // Obtem os Módulos
        $modules = Module::with(['resources', 'category'])
            ->whereIn('id', $moduleIds)
            ->get();
            
        // Verifica se encontrou módulos
        if ($modules->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Nenhum módulo encontrado'
            ];
        }

        /**
         * Payload dos módulos
         */
        $modulePayloads = [];

        /**
         * Payload de todos os resources
         */
        $resourcePayloads = [];

        // Faz looping em todos os módulos
        foreach ($modules as $module) {

            // Adiciona o módulo no payload
            $modulePayloads[] = [
                'name'     => $module->name,
                'category' => $module->category->name,
                'status'   => $status
            ];

            // Verifica se existe recursos
            if ($module->resources->isEmpty()) {
                continue;
            }

            // Faz looping em todos os recursos
            foreach ($module->resources as $resource) {

                // Adiciona o recurso no payload
                $resourcePayloads[] = [
                    'name'   => $resource->name,
                    'module' => $module->name,
                    'status' => $status
                ];

            }
        }

        /**
         * Envia módulos em paralelo
         */
        $this->guzzleService->pool(
            'put',
            'sistema/configurar-modulo',
            $tenant,
            $modulePayloads
        );

        /**
         * Envia todos os recursos em paralelo
         */
        if (!empty($resourcePayloads)) {
            $this->guzzleService->pool(
                'put',
                'sistema/configurar-permissao',
                $tenant,
                $resourcePayloads
            );
        }

        return ['success' => true];
    }

    /**
     * Libera os Recursos para o cliente no Micore
     * 
     * Pode ser enviado Manualmente
     * Ou Automaticamente quando o cliente gera a assinatura
     */
    public function configureFeatureForTenant($tenant, array $payloads)
    {
        return $this->guzzleService->pool(
            'put',
            'sistema/configurar-permissao',
            $tenant,
            $payloads
        );
    }

    /**
     * Cria o tempo da assinatura no MiCore
     */
    public function createSubscriptionCore($tenant, $startDate, $endDate)
    {
        return $this->guzzleService->request(
            'post',
            'sistema/atualizar-assinatura',
            $tenant,
            [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => true,
            ]
        );
    }
}