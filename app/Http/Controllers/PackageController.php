<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Module;
use App\Models\PackageModule;
use App\Models\ModulePricingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Package $content)
    {

        $this->request = $request;
        $this->repository = $content;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {

        // Obtém pacotes
        $packages = $this->repository
            ->with('modules')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Retorna a página
        return view('pages.packages.index')->with([
            'packages' => $packages,
        ]);
    }

    public function create()
    {

        // Obtém módulos
        $modules = Module::with('pricingTiers')->where('status', true)->get();

        // Retorna a página
        return view('pages.packages.create')->with([
            'modules' => $modules,
            'packageModuleConfigs' => [],
        ]);
    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Normaliza campos centrais
        $data['popular'] = (bool) ($data['popular'] ?? false);
        $data['duration_days'] = (int) ($data['duration_days'] ?? 30);
        $data['size_storage'] = (int) ($data['size_storage'] ?? 5368709120);
        $data['resources_list'] = $this->normalizeResourcesList($data['resources_list'] ?? null);

        // Autor
        $data['created_by'] = Auth::id();

        // Insere no banco de dados
        $created = $this->repository->create($data);

        $this->syncModules(
            $created,
            $request->input('module_items', []),
            (array) $request->input('prices', []),
            (array) $request->input('tier_prices', [])
        );

        $this->syncBenefits($created, $request->input('benefits', []));

        // Retorna a página
        return redirect()
            ->route('packages.index')
            ->with('message', 'Pacote <b>' . $created->name . '</b> adicionado com sucesso.');
    }

    public function edit($id)
    {
        // Obtém dados
        $package = $this->repository->with('benefits')->find($id);
        $modules = Module::with('pricingTiers')->where('status', true)->get();

        // Verifica se existe
        if (!$package) return redirect()->back();

        // Retorna a página
        return view('pages.packages.edit')->with([
            'package' => $package,
            'modules' => $modules,
            'packageModuleConfigs' => PackageModule::where('package_id', $id)
                ->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        // Verifica se existe
        if (!$package = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        $oldName = $package->name;

        // Normaliza campos centrais
        $data['popular'] = (bool) ($data['popular'] ?? false);
        $data['duration_days'] = (int) ($data['duration_days'] ?? ($package->duration_days ?? 30));
        $data['size_storage'] = (int) ($data['size_storage'] ?? ($package->size_storage ?? 5368709120));
        $data['resources_list'] = $this->normalizeResourcesList($data['resources_list'] ?? null);

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $package->update($data);

        $this->syncModules(
            $package,
            $request->input('module_items', []),
            (array) $request->input('prices', []),
            (array) $request->input('tier_prices', [])
        );

        $this->syncBenefits($package, $request->input('benefits', []));

        // Retorna a página
        return redirect()
            ->route('packages.edit', $id)
            ->with('message', 'Pacote <b>' . $oldName . '</b> atualizado para <b>' . $package->name . '</b> com sucesso.');
    }

    public function destroy($id)
    {

        // Obtém dados
        $package = $this->repository->find($id);

        // Atualiza status
        if ($package->status == 1) {
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('packages.index')
            ->with('message', 'Pacote <b>' . $package->name . '</b> ' . $message . ' com sucesso.');
    }

    public function updateOrder(Request $request, $id)
    {
        // Verifica se existe
        if (!$package = $this->repository->find($id)) return redirect()->back();

        // Valida ordem mínima
        $validated = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $package->update([
            'order' => (int) $validated['order'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('packages.index')
            ->with('message', 'Ordem do pacote <b>' . $package->name . '</b> atualizada com sucesso.');
    }

    private function syncBenefits(Package $package, array $benefits): void
    {
        $allowedColors = ['success', 'primary', 'info', 'warning'];

        $package->benefits()->delete();

        foreach ($benefits as $index => $benefit) {
            $icon = trim((string) ($benefit['icon'] ?? ''));
            $title = trim((string) ($benefit['title'] ?? ''));
            $label = trim((string) ($benefit['label'] ?? ''));
            $labelColor = strtolower(trim((string) ($benefit['label_color'] ?? 'primary')));

            if ($icon === '' && $title === '' && $label === '') {
                continue;
            }

            if ($icon === '' || $title === '') {
                continue;
            }

            if ($label === '') {
                $label = 'Ilimitado';
            }

            if (!in_array($labelColor, $allowedColors, true)) {
                $labelColor = 'primary';
            }

            $package->benefits()->create([
                'icon' => $icon,
                'title' => $title,
                'label' => $label,
                'label_color' => $labelColor,
                'position' => (int) $index,
            ]);
        }
    }

    private function syncModules(Package $package, array $moduleItems, array $prices = [], array $tierPrices = []): void
    {
        $packageId = $package->id;
        $createdBy = Auth::id();

        PackageModule::where('package_id', $packageId)->delete();

        foreach ($moduleItems as $row) {
            $moduleId = (int) ($row['module_id'] ?? 0);

            if ($moduleId <= 0) {
                continue;
            }

            $module = Module::find($moduleId);
            if (!$module) {
                continue;
            }

            $isUsagePricing = ($module->pricing_type ?? 'Preço Fixo') === 'Preço Por Uso';

            if ($isUsagePricing) {
                $moduleTiers = ModulePricingTier::where('module_id', $moduleId)->get();

                foreach ($moduleTiers as $tier) {
                    $tierRawPrice = $tierPrices[$tier->id] ?? null;
                    $tierPrice = $tierRawPrice !== null && $tierRawPrice !== ''
                        ? (float) toDecimal((string) $tierRawPrice)
                        : (float) $tier->price;

                    PackageModule::create([
                        'module_id' => $moduleId,
                        'package_id' => $packageId,
                        'module_pricing_tier_id' => (int) $tier->id,
                        'price' => $tierPrice,
                        'created_by' => $createdBy,
                    ]);
                }

                continue;
            }

            $moduleRawPrice = $prices[$moduleId] ?? null;
            $modulePrice = $moduleRawPrice !== null && $moduleRawPrice !== ''
                ? (float) toDecimal((string) $moduleRawPrice)
                : (float) $module->value;

            PackageModule::create([
                'module_id' => $moduleId,
                'package_id' => $packageId,
                'module_pricing_tier_id' => null,
                'price' => $modulePrice,
                'created_by' => $createdBy,
            ]);
        }
    }

    private function normalizeResourcesList($value): ?string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_map(fn ($line) => trim((string) $line), $lines ?: []);
        $lines = array_values(array_filter($lines, fn ($line) => $line !== ''));

        if (empty($lines)) {
            return null;
        }

        return implode(PHP_EOL, $lines);
    }
}
