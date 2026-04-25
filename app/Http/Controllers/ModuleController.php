<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\ModulePricingTier;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Module $content)
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
        // Carrega módulos com grupos e faixas de preço para exibição na listagem
        $modules = Module::with(['resources', 'pricingTiers', 'category', 'benefits'])
            ->orderByDesc('is_native')
            ->orderBy('name')
            ->get();

        // Retorna a página
        return view('pages.modules.index')->with([
            'modules' => $modules,
        ]);
    }

    public function create()
    {   
        // Obtém dados dos Grupos ativos
        $resources = Resource::where('status', true)->get(); 
        $categories = ModuleCategory::where('status', true)->get();

        // Retorna a página
        return view('pages.modules.create')->with([
            'resources' => $resources,
            'categories' => $categories,
        ]);

    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = Auth::id();

        // Define o tipo de cobrança (fixo por padrão)
        $data['pricing_type'] = $data['pricing_type'];
        $data['is_native'] = $request->boolean('is_native');

        if ($data['pricing_type'] === 'Preço Por Uso') {
            // Em preço por uso, o valor fixo não é utilizado
            $data['value'] = 0;
        } else {
            // Em preço fixo, normaliza o valor monetário
            $data['value'] = toDecimal($data['value']);
        }

        // Insere no banco de dados
        $created = $this->repository->create($data);

        if (isset($data['resources'])) {
            Resource::where('module_id', $created->id)->update(['module_id' => null]);
            Resource::whereIn('id', $data['resources'])->update(['module_id' => $created->id]);
        }

        // Persiste as faixas de preço quando o tipo é por uso
        $this->syncPricingTiers($created, $request->input('tiers', []), $data['pricing_type']);
        $this->syncBenefits($created, $request->input('benefits', []));

        // Salva capa do módulo, se enviada
        if ($request->hasFile('cover_image')) {
            $this->saveCoverImage($created, $request->file('cover_image'));
        }

            // Retorna a página
            return redirect()
                    ->route('modules.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function editPrices()
    {
        $modules = Module::where('status', true)
            ->where('is_native', false)
            ->orderBy('name')
            ->with(['pricingTiers' => function ($query) {
                $query->orderBy('usage_limit');
            }])
            ->get(['id', 'name', 'value', 'pricing_type', 'usage_label']);

        return view('pages.modules.update-prices')->with([
            'modules' => $modules,
        ]);
    }

    public function updatePrices(Request $request)
    {
        $validated = $request->validate([
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'string'],
            'tier_prices' => ['nullable', 'array'],
            'tier_prices.*' => ['nullable', 'string'],
        ]);

        $prices = $validated['prices'] ?? [];
        $tierPrices = $validated['tier_prices'] ?? [];
        $moduleIds = array_map('intval', array_keys($prices));
        $tierIds = array_map('intval', array_keys($tierPrices));

        if (empty($moduleIds) && empty($tierIds)) {
            return redirect()
                ->route('modules.prices.edit')
                ->with('message', 'Nenhum preço informado para atualização.');
        }

        $modules = Module::where('status', true)
            ->where('is_native', false)
            ->whereIn('id', $moduleIds)
            ->get(['id', 'value']);

        $updated = 0;

        foreach ($modules as $module) {
            $rawValue = trim((string) ($prices[$module->id] ?? ''));

            if ($rawValue === '' || !preg_match('/\d/', $rawValue)) {
                continue;
            }

            $module->value = toDecimal($rawValue);
            $module->updated_by = Auth::id();
            $module->save();

            $updated++;
        }

        $tiers = ModulePricingTier::whereIn('id', $tierIds)
            ->whereHas('module', function ($query) {
                $query->where('status', true)
                    ->where('is_native', false);
            })
            ->get(['id', 'price']);

        foreach ($tiers as $tier) {
            $rawValue = trim((string) ($tierPrices[$tier->id] ?? ''));

            if ($rawValue === '' || !preg_match('/\d/', $rawValue)) {
                continue;
            }

            $tier->price = toDecimal($rawValue);
            $tier->save();

            $updated++;
        }

        return redirect()
            ->route('modules.prices.edit')
            ->with('message', $updated > 0
                ? "Preços atualizados com sucesso para {$updated} módulo(s)."
                : 'Nenhum preço válido foi informado para atualização.');
    }

    public function edit($id)
    {
        // Obtém dados dos Recursos ativos
        $resources = Resource::where('status', true)->get();        
        $categories = ModuleCategory::where('status', true)->get();

        // Obtém dados
        $modules = $this->repository->with('benefits')->find($id);

        // Verifica se existe
        if(!$modules) return redirect()->back();

        // Retorna a página
        return view('pages.modules.edit')->with([
            'modules' => $modules,
            'resources' => $resources,
            'categories' => $categories,
        ]);

    }
    
    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$modules = $this->repository->find($id)) return redirect()->back();

        // Armazena o nome antigo
        $oldName = $modules->name;

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();
        
        // Define o tipo de cobrança (fixo por padrão)
        $data['pricing_type'] = $data['pricing_type'];
        $data['is_native'] = $request->boolean('is_native');

        if ($data['pricing_type'] === 'Preço Por Uso') {
            // Em preço por uso, o valor fixo não é utilizado
            $data['value'] = 0;
        } else {
            // Em preço fixo, normaliza o valor monetário
            $data['value'] = toDecimal($data['value']);
        }

        // Atualiza dados
        $modules->update($data);

        if (isset($data['resources'])) {
            Resource::where('module_id', $modules->id)->update(['module_id' => null]);
            Resource::whereIn('id', $data['resources'])->update(['module_id' => $modules->id]);
        }

        // Atualiza as faixas de preço quando o tipo é por uso
        $this->syncPricingTiers($modules, $request->input('tiers', []), $data['pricing_type']);
        $this->syncBenefits($modules, $request->input('benefits', []));

        // Atualiza capa do módulo, se enviada
        if ($request->hasFile('cover_image')) {
            $this->saveCoverImage($modules, $request->file('cover_image'));
        }

        // Retorna a página
        return redirect()
            ->route('modules.index')
            ->with('message', 'Setor <b>'. $oldName . '</b> atualizado para <b>'. $modules->name .'</b> com sucesso.');
        
    }

    // Sincroniza as faixas de preço do módulo quando a cobrança é por uso
    private function syncPricingTiers(Module $module, array $tiers, string $pricingType): void
    {
        $isUsage = $pricingType === 'Preço Por Uso';

        if (!$isUsage) {
            // Se não for por uso, remove faixas antigas
            $module->pricingTiers()->delete();
            return;
        }

        // Limpa e recria para evitar inconsistências
        $module->pricingTiers()->delete();

        foreach ($tiers as $tier) {
            $limitRaw = $tier['limit'] ?? null;
            $priceRaw = $tier['price'] ?? null;

            if ($limitRaw === null || $priceRaw === null) {
                continue;
            }

            // Normaliza inputs e ignora valores inválidos
            $limit = (int) preg_replace('/\D/', '', (string) $limitRaw);
            $price = toDecimal($priceRaw);

            if ($limit <= 0 || (float) $price <= 0) {
                continue;
            }

            // Cria faixa válida
            $module->pricingTiers()->create([
                'usage_limit' => $limit,
                'price' => $price,
            ]);
        }
    }

    // Sincroniza os benefícios exibidos no card do módulo
    private function syncBenefits(Module $module, array $benefits): void
    {
        $allowedColors = ['success', 'primary', 'info', 'warning'];

        $module->benefits()->delete();

        foreach ($benefits as $index => $benefit) {
            $icon = trim((string) ($benefit['icon'] ?? ''));
            $title = trim((string) ($benefit['title'] ?? ''));
            $label = trim((string) ($benefit['label'] ?? ''));
            $labelColor = strtolower(trim((string) ($benefit['label_color'] ?? 'primary')));

            // Ignora linhas vazias ou incompletas para não persistir dados inválidos.
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

    private function saveCoverImage(Module $module, $coverImage): void
    {
        if (!$coverImage || !$coverImage->isValid()) {
            return;
        }

        $extension = $coverImage->getClientOriginalExtension();
        $filename = $extension ? "cover.{$extension}" : 'cover';
        $path = "modules/{$module->id}";

        $coverImage->storeAs($path, $filename, 'public');
        $module->cover_image = $filename;
        $module->save();
    }

    public function destroy($id)
    {

        // Obtém dados
        $modules = $this->repository->find($id);

        // Atualiza status
        if($modules->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('modules.index')
            ->with('message', 'Setor <b>'. $modules->name . '</b> '. $message .' com sucesso.');

    }

}
