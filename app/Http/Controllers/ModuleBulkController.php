<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JsonException;

class ModuleBulkController extends Controller
{
    /**
     * Página de importação em massa de módulos via JSON.
     */
    public function bulkPage()
    {
        return view('pages.modules.bulk')->with([
            'modulesBulkTemplate' => $this->buildModulesBulkTemplate(),
        ]);
    }

    /**
     * Exporta um modelo JSON para edição em massa de descrição e benefícios.
     */
    public function bulkTemplate()
    {
        return response()->json(
            $this->buildModulesBulkTemplate(),
            200,
            [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Importa JSON para atualização em massa de módulos.
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'bulk_modules_json' => ['required', 'string'],
        ]);

        $payloadRaw = trim((string) $request->input('bulk_modules_json'));

        try {
            $payload = json_decode($payloadRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return redirect()
                ->route('modules.bulk.page')
                ->withInput()
                ->with('message', 'JSON inválido. Verifique a estrutura e tente novamente.');
        }

        $modulesPayload = $payload['modules'] ?? null;
        if (!is_array($modulesPayload)) {
            return redirect()
                ->route('modules.bulk.page')
                ->withInput()
                ->with('message', 'JSON inválido: a chave "modules" deve ser um array.');
        }

        $allowedColors = ['success', 'primary', 'info', 'warning'];
        $updatedCount = 0;
        $notFound = [];

        DB::transaction(function () use ($modulesPayload, $allowedColors, &$updatedCount, &$notFound) {
            foreach ($modulesPayload as $moduleItem) {
                if (!is_array($moduleItem)) {
                    continue;
                }

                $id = (int) ($moduleItem['id'] ?? 0);

                if ($id <= 0) {
                    continue;
                }

                $module = Module::query()
                    ->where('id', $id)
                    ->first();

                if (!$module) {
                    $notFound[] = "id:{$id}";
                    continue;
                }

                $description = array_key_exists('description', $moduleItem)
                    ? trim((string) ($moduleItem['description'] ?? ''))
                    : $module->description;

                $benefits = is_array($moduleItem['benefits'] ?? null) ? $moduleItem['benefits'] : [];
                $normalizedBenefits = array_map(function ($benefit) use ($allowedColors) {
                    $icon = trim((string) ($benefit['icon'] ?? ''));
                    $title = trim((string) ($benefit['title'] ?? ''));
                    $label = trim((string) ($benefit['label'] ?? ''));
                    $labelColor = strtolower(trim((string) ($benefit['label_color'] ?? 'primary')));

                    if (!in_array($labelColor, $allowedColors, true)) {
                        $labelColor = 'primary';
                    }

                    return [
                        'icon' => $icon,
                        'title' => $title,
                        'label' => $label,
                        'label_color' => $labelColor,
                    ];
                }, $benefits);

                $module->update([
                    'description' => $description,
                    'updated_by' => Auth::id(),
                ]);

                // Regra da importação: remove benefícios atuais e cria novamente a partir do JSON.
                $this->syncBenefits($module, $normalizedBenefits);
                $updatedCount++;
            }
        });

        $message = "{$updatedCount} módulo(s) atualizado(s) com sucesso.";
        if (!empty($notFound)) {
            $message .= ' Não encontrados: ' . implode(', ', $notFound) . '.';
        }

        return redirect()
            ->route('modules.bulk.page')
            ->with('message', $message);
    }

    /**
     * Monta payload base para edição em massa por IA.
     */
    private function buildModulesBulkTemplate(): array
    {
        $modules = Module::with('benefits')
            ->orderByDesc('is_native')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description']);

        return [
            'meta' => [
                'instructions' => 'Atualize apenas description e benefits. Use sempre o id para localizar cada módulo. Ao importar, os benefícios atuais do módulo serão removidos e recriados com base neste JSON.',
            ],
            'modules' => $modules->map(function (Module $module) {
                return [
                    'id' => (int) $module->id,
                    'name' => (string) $module->name,
                    'description' => (string) ($module->description ?? ''),
                    'benefits' => $module->benefits->map(function ($benefit) {
                        return [
                            'icon' => (string) ($benefit->icon ?? ''),
                            'title' => (string) ($benefit->title ?? ''),
                            'label' => (string) ($benefit->label ?? ''),
                            'label_color' => (string) ($benefit->label_color ?? 'primary'),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    /**
     * Sincroniza os benefícios exibidos no card do módulo.
     */
    private function syncBenefits(Module $module, array $benefits): void
    {
        $allowedColors = ['success', 'primary', 'info', 'warning'];

        $module->benefits()->delete();

        foreach ($benefits as $index => $benefit) {
            $icon = trim((string) ($benefit['icon'] ?? ''));
            $title = trim((string) ($benefit['title'] ?? ''));
            $label = trim((string) ($benefit['label'] ?? ''));
            $labelColor = strtolower(trim((string) ($benefit['label_color'] ?? 'primary')));

            if ($icon === '' && $title === '' && $label === '') {
                continue;
            }

            if ($icon === '' || $title === '' || $label === '') {
                continue;
            }

            if (!in_array($labelColor, $allowedColors, true)) {
                $labelColor = 'primary';
            }

            $module->benefits()->create([
                'icon' => $icon,
                'title' => $title,
                'label' => $label,
                'label_color' => $labelColor,
                'position' => (int) $index,
            ]);
        }
    }
}
