<?php

namespace App\Http\Controllers;

use App\Models\TenantIntegration;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantIntegrationController extends Controller
{
    public function index(): View
    {
        return view('pages.tenants_integrations.index');
    }

    public function debugToken($id)
    {
        // Obtem o tenant integration
        $integration = TenantIntegration::find($id);

        // Verifica se o tenant integration existe
        if (!$integration) {
            return response()->json([
                'success' => false,
                'message' => 'Integração não encontrada',
            ]);
        }

        // Verifica se o tenant integration é da meta
        if ($integration->provider != 'meta') {
            return response()->json([
                'success' => false,
                'message' => 'Integração não é da meta',
            ]);
        }

        // Verifica se o tenant integration tem token
        if (!$integration->access_token) {
            return response()->json([
                'success' => false,
                'message' => 'Integração não tem token',
            ]);
        }

        // Inicia o serviço da meta
        $metaService = new MetaApiService();

        // Consulta escopos e expiração reais do token para persistência.
        $debug = $metaService->debugToken($integration->access_token);

        // Verifica se foi um sucesso
        if($debug['success'] && isset($debug['data']['data'])) {
            return response()->json($debug['data']['data']);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao depurar token',
        ]);
    }
}
