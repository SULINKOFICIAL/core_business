<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\NotifyErrorRequest;
use App\Models\ErrorMiCore;
use App\Models\IntegrationSuggestion;
use App\Models\Module;
use App\Models\Package;
use App\Services\SystemProblemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApisUtilityController extends Controller
{
    /**
     * Registra erro técnico vindo dos tenants e dispara notificação.
     * Centraliza validação do payload de telemetria da API.
     */
    public function notifyErrors(NotifyErrorRequest $request, SystemProblemNotificationService $systemProblemNotificationService): JsonResponse
    {
        $data = $request->validated();

        // O tenant injetado pelo middleware prevalece sobre tenant_id recebido.
        $tenant = $request->input('tenant');
        $data['tenant_id'] = $tenant->id ?? ($data['tenant_id'] ?? null);

        $error = ErrorMiCore::create($data);

        // Reaproveita event_date do payload; se ausente, usa timestamp de processamento.
        $notification = $systemProblemNotificationService->notify(array_merge($data, [
            'error_id' => $error->id,
            'event_date' => $data['event_date'] ?? now()->format('d/m/Y H:i:s'),
        ]));

        return response()->json([
            'message' => 'Registrou o erro',
            'id' => $error->id,
            'notification' => $notification,
        ], 201);
    }

    /**
     * Persiste sugestão funcional enviada pelo tenant autenticado.
     * Mantém retorno simples para integração de front-end.
     */
    public function suggestions(Request $request): JsonResponse
    {
        IntegrationSuggestion::create($request->all());
        return response()->json('Sugestão enviada com sucesso!', 201);
    }

    /**
     * Retorna módulos ativos e seus respectivos dados de precificação.
     * Preserva contrato atual consumido pelos sistemas satélites.
     */
    public function modules(): JsonResponse
    {
        $modules = Module::with(['category', 'pricingTiers', 'benefits'])->where('status', true)->get();
        $moduleJson = [];

        foreach ($modules as $module) {
            $moduleData = [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'category' => $module->category?->name,
                'cover_image' => $module->cover_image
                ? asset('storage/modules/' . $module->id . '/' . $module->cover_image)
                : asset('assets/media/images/default.png'),
                'pricing' => [
                    'type' => $module->pricing_type,
                ],
                'benefits' => $module->benefits->map(function ($benefit) {
                    return [
                        'icon' => $benefit->icon,
                        'title' => $benefit->title,
                        'label' => $benefit->label,
                        'label_color' => $benefit->label_color,
                    ];
                })->values()->toArray(),
            ];

            if ($module->pricing_type === 'Preço Por Uso') {
                // Em preço por uso, retorna tiers ordenados por limite de utilização.
                $pricingTiers = $module->pricingTiers->sortBy('usage_limit')
                    ->values()
                    ->map(function ($tier) {
                        return [
                            'usage_limit' => $tier->usage_limit,
                            'price' => (float) $tier->price,
                        ];
                    })
                    ->toArray();

                $moduleData['pricing']['values'] = $pricingTiers;
            } else {
                $moduleData['pricing']['values'] = (float) $module->value;
            }

            $moduleJson[] = $moduleData;
        }

        return response()->json($moduleJson, 200);
    }

    /**
     * Retorna pacotes ativos com módulos e benefícios para seleção no checkout.
     */
    public function packages(): JsonResponse
    {
        $packages = Package::with(['modules', 'benefits'])->where('status', true)->orderBy('order')->get();
        $packageJson = [];

        foreach ($packages as $package) {
            $packageJson[] = [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'popular' => (bool) ($package->popular ?? false),
                'value' => (float) $package->value,
                'duration_days' => (int) $package->duration_days,
                'benefits' => $package->benefits->map(function ($benefit) {
                    return [
                        'icon' => $benefit->icon,
                        'title' => $benefit->title,
                        'label' => $benefit->label,
                        'label_color' => $benefit->label_color,
                    ];
                })->values()->toArray(),
                'modules' => $package->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                    ];
                })->values()->toArray(),
            ];
        }

        return response()->json($packageJson, 200);
    }
}
