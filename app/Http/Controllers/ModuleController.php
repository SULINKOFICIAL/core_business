<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleCategory;
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
        $modules = Module::with(['groups', 'pricingTiers', 'category'])->get();

        // Retorna a página
        return view('pages.modules.index')->with([
            'modules' => $modules,
        ]);
    }

    public function create()
    {   
        // Obtém dados dos Grupos ativos
        $groups = Group::where('status', true)->get(); 
        $categories = ModuleCategory::where('status', true)->get();

        // Retorna a página
        return view('pages.modules.create')->with([
            'groups' => $groups,
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
        $data['pricing_type'] = $data['pricing_type'] ?? 'fixed';

        if ($data['pricing_type'] === 'usage') {
            // Em preço por uso, o valor fixo não é utilizado
            $data['value'] = 0;
        } else {
            // Em preço fixo, normaliza o valor monetário
            $data['value'] = toDecimal($data['value']);
        }

        // Insere no banco de dados
        $created = $this->repository->create($data);

        if (isset($data['groups'])) {
            $created->groups()->sync($data['groups']);
        }

        // Persiste as faixas de preço quando o tipo é por uso
        $this->syncPricingTiers($created, $request->input('tiers', []), $data['pricing_type']);

        // Salva capa do módulo, se enviada
        if ($request->hasFile('cover_image')) {
            $this->saveCoverImage($created, $request->file('cover_image'));
        }

            // Retorna a página
            return redirect()
                    ->route('modules.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function edit($id)
    {
        // Obtém dados dos Grupos ativos
        $groups = Group::where('status', true)->get();        
        $categories = ModuleCategory::where('status', true)->get();

        // Obtém dados
        $modules = $this->repository->find($id);

        // Verifica se existe
        if(!$modules) return redirect()->back();

        // Retorna a página
        return view('pages.modules.edit')->with([
            'modules' => $modules,
            'groups' => $groups,
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
        $data['pricing_type'] = $data['pricing_type'] ?? 'fixed';

        if ($data['pricing_type'] === 'usage') {
            // Em preço por uso, o valor fixo não é utilizado
            $data['value'] = 0;
        } else {
            // Em preço fixo, normaliza o valor monetário
            $data['value'] = toDecimal($data['value']);
        }

        // Atualiza dados
        $modules->update($data);

        if (isset($data['groups'])) {
            $modules->groups()->sync($data['groups']);
        }

        // Atualiza as faixas de preço quando o tipo é por uso
        $this->syncPricingTiers($modules, $request->input('tiers', []), $data['pricing_type']);

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
        if ($pricingType !== 'usage') {
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
