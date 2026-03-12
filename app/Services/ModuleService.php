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

    /**
     * Libera os Módulos para o cliente no Micore
     * 
     * Pode ser enviado Manualmente
     * Ou Automaticamente quando o cliente gera a assinatura
     */
    public function configureModule($client, array $data)
    {
        /**
         * Envia para o Micore o Modulo com a Categoria
         */
        $response = $this->guzzleService->request(
            'put',
            'sistema/configurar-modulo',
            $client,
            [
                'name'     => $data['name'],
                'category' => $data['category'],
                'status'   => $data['status']
            ]
        );

        // Verifica se veio sucesso da requisição
        if (!($response['success'] ?? false)) {
            return $response;
        }

        /**
         * Busca módulo com recursos
         */
        $module = Module::where('name', $data['name'])
            ->whereHas('category', function ($query) use ($data) {
                $query->where('name', $data['category']);
            })
            ->with(['resources', 'category'])
            ->first();

        /**
         * Envia recursos
         */
        foreach ($module->resources as $resource) {
            $this->configureFeatureForClient(
                $client,
                $resource->name,
                $module->name,
                $data['status']
            );
        }

        return $response;
    }

    /**
     * Libera os Recursos para o cliente no Micore
     * 
     * Pode ser enviado Manualmente
     * Ou Automaticamente quando o cliente gera a assinatura
     */
    public function configureFeatureForClient($client, $resourceName, $moduleName, $status)
    {
        /**
         * Envia para o Micore o Recurso com o Modulo
         */
        return $this->guzzleService->request(
            'put',
            'sistema/configurar-permissao',
            $client,
            [
                'name'   => $resourceName,
                'module' => $moduleName,
                'status' => $status
            ]
        );
    }
}