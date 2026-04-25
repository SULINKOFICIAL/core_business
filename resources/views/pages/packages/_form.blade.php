@php
    $benefits = old('benefits');

    if (is_array($benefits)) {
        $benefits = array_map(function ($benefit) {
            return [
                'icon' => trim((string) ($benefit['icon'] ?? '')),
                'title' => trim((string) ($benefit['title'] ?? '')),
                'label' => trim((string) ($benefit['label'] ?? '')),
                'label_color' => strtolower(trim((string) ($benefit['label_color'] ?? 'primary'))),
            ];
        }, $benefits);
    }

    if ($benefits === null && isset($package) && method_exists($package, 'benefits')) {
        $benefits = $package->benefits->map(function ($benefit) {
            return [
                'icon' => $benefit->icon,
                'title' => $benefit->title,
                'label' => $benefit->label,
                'label_color' => $benefit->label_color,
            ];
        })->toArray();
    }

	    if (empty($benefits)) {
	        $benefits = [[
	            'icon' => 'shop',
	            'title' => 'Vendas e Pedidos',
	            'label' => 'Ilimitado',
	            'label_color' => 'primary',
	        ]];
	    }

    $resourcesList = old('resources_list', $package->resources_list ?? '');

    $initialSelectedModules = old('module_items');

    if (!is_array($initialSelectedModules)) {
        $initialSelectedModules = [];

        if (isset($package)) {
            foreach ($package->modules as $module) {
                $moduleId = (int) $module->id;
                $config = $packageModuleConfigs[$moduleId] ?? null;

                $initialSelectedModules[] = [
                    'module_id' => $moduleId,
                    'module_pricing_tier_id' => $config ? (int) ($config->module_pricing_tier_id ?? 0) : 0,
                ];
            }
        }
    } else {
        $initialSelectedModules = array_map(function ($row) {
            return [
                'module_id' => (int) ($row['module_id'] ?? 0),
                'module_pricing_tier_id' => (int) ($row['module_pricing_tier_id'] ?? 0),
            ];
        }, $initialSelectedModules);
    }

    $selectedModuleIds = collect($initialSelectedModules)
        ->pluck('module_id')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    $moduleCatalog = collect($modules)->map(function ($module) {
        return [
            'id' => (int) $module->id,
            'name' => $module->name,
            'description' => $module->description,
            'pricing_type' => $module->pricing_type,
            'is_usage' => ($module->pricing_type === 'Preço Por Uso'),
            'value' => (float) $module->value,
            'value_formatted' => 'R$ ' . number_format((float) $module->value, 2, ',', '.'),
            'tiers' => $module->pricingTiers->sortBy('usage_limit')->values()->map(function ($tier) {
                return [
                    'id' => (int) $tier->id,
                    'usage_limit' => (int) $tier->usage_limit,
                    'price' => (float) $tier->price,
                    'price_formatted' => 'R$ ' . number_format((float) $tier->price, 2, ',', '.'),
                ];
            })->all(),
        ];
    })->values()->all();
@endphp

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Informações gerais</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
                <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $package->name ?? old('name') }}" required>
            </div>
            <div class="col-3 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
                <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($package->value ?? 0), 2, ',', '.') }}" required>
            </div>
            <div class="col-3 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">É popular?</label>
                <select name="popular" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Selecione" required>
                    <option value="0" @selected((int) old('popular', $package->popular ?? 0) === 0)>Não</option>
                    <option value="1" @selected((int) old('popular', $package->popular ?? 0) === 1)>Sim</option>
                </select>
            </div>
            <div class="col-3 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Ordem</label>
                <input type="text" class="form-control form-control-solid" name="order" value="{{ $package->order ?? 1 }}" required>
            </div>
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Descrição</label>
                <textarea
                    class="form-control form-control-solid"
                    rows="4"
                    maxlength="255"
                    placeholder="Descrição do pacote"
                    name="description"
                >{{ old('description', $package->description ?? '') }}</textarea>
                <div class="form-text">Máximo de 255 caracteres.</div>
            </div>
        </div>

        {{-- Mantém campos legados sem exibir no formulário --}}
        <input type="hidden" name="duration_days" value="{{ old('duration_days', $package->duration_days ?? 30) }}">
        <input type="hidden" name="size_storage" value="{{ old('size_storage', $package->size_storage ?? 5368709120) }}">
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Módulos do pacote</h3>
    </div>
    <div class="card-body">
        <div class="row" id="package-modules-grid">
            @foreach ($modules as $module)
                @php
                    $isSelected = in_array((int) $module->id, $selectedModuleIds, true);
                    $isUsage = ($module->pricing_type === 'Preço Por Uso');
                    $priceLabel = $isUsage
                        ? 'Preço por uso'
                        : 'R$ ' . number_format((float) $module->value, 2, ',', '.');
                @endphp
                <div class="col-md-4 mb-4">
                    <div class="card border border-gray-300 h-100 cursor-pointer package-module-card {{ $isSelected ? 'border-primary' : '' }}" data-module-id="{{ $module->id }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <p class="text-gray-800 fw-bolder mb-0">{{ $module->name }}</p>
                                <span class="badge badge-light-primary package-module-selected-badge" @if (!$isSelected) style="display:none" @endif>Selecionado</span>
                            </div>
                            @if (!empty($module->description))
                                <p class="text-gray-600 fs-8 mb-2">{{ $module->description }}</p>
                            @endif
                            <p class="text-gray-700 fw-bolder fs-6 mb-0">{{ $priceLabel }}</p>
                            @if ($isUsage)
                                <span class="badge badge-light-warning mt-2">Requer limite (tiers)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="separator my-6"></div>

        <h4 class="mb-3">Módulos inclusos</h4>
        <div class="table-responsive">
            <table class="table table-row-dashed align-middle gs-0 gy-2" id="selected-modules-table">
                <thead>
                    <tr class="fw-bolder text-muted">
                        <th>Módulo</th>
                        <th class="text-end">Preço</th>
                        <th class="text-end">Limite do pacote</th>
                    </tr>
                </thead>
                <tbody id="selected-modules-summary"></tbody>
            </table>
        </div>
        <div id="selected-modules-empty" class="text-muted fs-7">Nenhum módulo selecionado.</div>
        <div id="module-items-inputs"></div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Recursos do pacote</h3>
    </div>
    <div class="card-body">
        <p class="text-gray-600 mb-3">Adicione 1 recurso por linha. Esses itens serão exibidos no card do pacote.</p>
        <textarea
            class="form-control form-control-solid"
            name="resources_list"
            rows="8"
            placeholder="Ex:&#10;CRM - Negócios&#10;CRM - Funis de Venda&#10;CRM - Marcos de Progresso"
        >{{ $resourcesList }}</textarea>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Benefícios</h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <p class="text-gray-600 mb-0">Itens exibidos no card do pacote.</p>
            <button type="button" class="btn btn-light-primary" id="add-package-benefit">Adicionar benefício</button>
        </div>

        <div id="package-benefits">
            @foreach ($benefits as $index => $benefit)
            <div class="row align-items-end package-benefit-row mb-3 border border-gray-200 rounded p-4">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Ícone</label>
                    <input
                        type="hidden"
                        id="package-benefit-icon-{{ $index }}"
                        name="benefits[{{ $index }}][icon]"
                        value="{{ $benefit['icon'] }}"
                    >
                    <button
                        type="button"
                        class="btn btn-light-primary w-100 d-flex align-items-center justify-content-center gap-2 mc-select-icon"
                        data-icon-target="#package-benefit-icon-{{ $index }}"
                        data-required-icon="false"
                        title="Selecionar ícone"
                    >
                        <i class="{{ $benefit['icon'] ?: 'fa-solid fa-icons text-muted' }}"></i>
                        <span>Selecionar ícone</span>
                    </button>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Título</label>
                    <input type="text" class="form-control form-control-solid" name="benefits[{{ $index }}][title]" placeholder="Ex: Vendas e Pedidos" value="{{ $benefit['title'] }}">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Label</label>
                    <input type="text" class="form-control form-control-solid" name="benefits[{{ $index }}][label]" placeholder="Ex: Ilimitado" value="{{ $benefit['label'] }}">
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Cor do label</label>
                    <select class="form-select form-select-solid" name="benefits[{{ $index }}][label_color]">
                        <option value="success" @selected(($benefit['label_color'] ?? 'primary') === 'success')>success</option>
                        <option value="primary" @selected(($benefit['label_color'] ?? 'primary') === 'primary')>primary</option>
                        <option value="info" @selected(($benefit['label_color'] ?? 'primary') === 'info')>info</option>
                        <option value="warning" @selected(($benefit['label_color'] ?? 'primary') === 'warning')>warning</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-light-danger w-100 remove-package-benefit">Remover</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@section('custom-footer')
    @parent
    <script>
        $(function () {
            const moduleCatalog = @json($moduleCatalog);
            let selectedModules = @json($initialSelectedModules);

            const benefitsContainer = $('#package-benefits');
            const addBenefitButton = $('#add-package-benefit');
            const summaryBody = $('#selected-modules-summary');
            const emptySummary = $('#selected-modules-empty');
            const moduleItemsInputs = $('#module-items-inputs');

            function normalizeSelectedModules() {
                const validIds = moduleCatalog.map((module) => Number(module.id));

                selectedModules = selectedModules
                    .map((row) => ({
                        module_id: Number(row.module_id || 0),
                        module_pricing_tier_id: Number(row.module_pricing_tier_id || 0),
                    }))
                    .filter((row, index, list) => row.module_id > 0 && validIds.includes(row.module_id) && list.findIndex((item) => item.module_id === row.module_id) === index);
            }

            function findModule(moduleId) {
                return moduleCatalog.find((module) => Number(module.id) === Number(moduleId)) || null;
            }

            function formatCurrency(value) {
                return (Number(value) || 0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            }

            function selectedMap() {
                const map = {};
                selectedModules.forEach((item) => {
                    map[Number(item.module_id)] = item;
                });
                return map;
            }

            function syncCardsVisual() {
                const map = selectedMap();

                $('.package-module-card').each(function () {
                    const card = $(this);
                    const moduleId = Number(card.data('module-id'));
                    const selected = !!map[moduleId];

                    card.toggleClass('border-primary', selected);
                    card.find('.package-module-selected-badge').toggle(selected);
                });
            }

            function buildTierSelect(module, selectedTierId) {
                if (!module.is_usage) {
                    return {
                        html: '<span class="text-muted">-</span>',
                        selectedTierId: 0,
                        selectedTier: null,
                    };
                }

                const tiers = Array.isArray(module.tiers) ? module.tiers : [];
                const fallbackTierId = tiers.length ? Number(tiers[0].id) : 0;
                const finalTierId = tiers.some((tier) => Number(tier.id) === Number(selectedTierId))
                    ? Number(selectedTierId)
                    : fallbackTierId;

                const selectedTier = tiers.find((tier) => Number(tier.id) === finalTierId) || null;

                if (!tiers.length) {
                    return {
                        html: '<span class="text-muted">Sem tiers</span>',
                        selectedTierId: 0,
                        selectedTier: null,
                    };
                }

                const options = tiers.map((tier) => {
                    const tierId = Number(tier.id);
                    const selected = tierId === finalTierId ? 'selected' : '';
                    return '<option value="' + tierId + '" ' + selected + '>Até ' + tier.usage_limit + ' - ' + tier.price_formatted + '</option>';
                }).join('');

                return {
                    html: '<select class="form-select form-select-sm form-select-solid package-module-tier-select" data-module-id="' + module.id + '">' + options + '</select>',
                    selectedTierId: finalTierId,
                    selectedTier,
                };
            }

            function renderSelectedModules() {
                normalizeSelectedModules();

                summaryBody.empty();
                moduleItemsInputs.empty();

                if (!selectedModules.length) {
                    emptySummary.show();
                    return;
                }

                emptySummary.hide();

                selectedModules.forEach((item, index) => {
                    const module = findModule(item.module_id);
                    if (!module) return;

                    const tierState = buildTierSelect(module, item.module_pricing_tier_id);
                    item.module_pricing_tier_id = tierState.selectedTierId;

                    const priceText = module.is_usage
                        ? (tierState.selectedTier ? tierState.selectedTier.price_formatted : formatCurrency(0))
                        : module.value_formatted;

                    const rowHtml = [
                        '<tr>',
                        '  <td class="text-gray-800 fw-bold">' + module.name + '</td>',
                        '  <td class="text-end text-gray-700 fw-bold">' + priceText + '</td>',
                        '  <td class="text-end">' + tierState.html + '</td>',
                        '</tr>'
                    ].join('');

                    summaryBody.append(rowHtml);

                    moduleItemsInputs.append('<input type="hidden" name="module_items[' + index + '][module_id]" value="' + module.id + '">');
                    moduleItemsInputs.append('<input type="hidden" name="module_items[' + index + '][module_pricing_tier_id]" value="' + (item.module_pricing_tier_id || 0) + '">');
                });
            }

            function toggleModule(moduleId) {
                const module = findModule(moduleId);
                if (!module) return;

                const existingIndex = selectedModules.findIndex((item) => Number(item.module_id) === Number(moduleId));

                if (existingIndex >= 0) {
                    selectedModules.splice(existingIndex, 1);
                } else {
                    const defaultTierId = module.is_usage && module.tiers.length
                        ? Number(module.tiers[0].id)
                        : 0;

                    selectedModules.push({
                        module_id: Number(module.id),
                        module_pricing_tier_id: defaultTierId,
                    });
                }

                syncCardsVisual();
                renderSelectedModules();
            }

            function nextBenefitIndex() {
                return benefitsContainer.length ? benefitsContainer.find('.package-benefit-row').length : 0;
            }

            function addBenefitRow() {
                if (!benefitsContainer.length) return;
                const index = nextBenefitIndex();
                const iconInputId = 'package-benefit-icon-new-' + Date.now() + '-' + index;

                const rowHtml = [
                    '<div class="row align-items-end package-benefit-row mb-3 border border-gray-200 rounded p-4">',
                    '  <div class="col-md-3 mb-3 mb-md-0">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Ícone</label>',
                    '    <input type="hidden" id="' + iconInputId + '" name="benefits[' + index + '][icon]" value="">',
                    '    <button type="button" class="btn btn-light-primary w-100 d-flex align-items-center justify-content-center gap-2 mc-select-icon" data-icon-target="#' + iconInputId + '" data-required-icon="false" title="Selecionar ícone">',
                    '      <i class="fa-solid fa-icons text-muted"></i>',
                    '      <span>Selecionar ícone</span>',
                    '    </button>',
                    '  </div>',
                    '  <div class="col-md-3 mb-3 mb-md-0">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Título</label>',
                    '    <input type="text" class="form-control form-control-solid" name="benefits[' + index + '][title]" placeholder="Ex: Vendas e Pedidos" value="">',
                    '  </div>',
                    '  <div class="col-md-3 mb-3 mb-md-0">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Label</label>',
                    '    <input type="text" class="form-control form-control-solid" name="benefits[' + index + '][label]" placeholder="Ex: Ilimitado" value="">',
                    '  </div>',
                    '  <div class="col-md-2 mb-3 mb-md-0">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Cor do label</label>',
                    '    <select class="form-select form-select-solid" name="benefits[' + index + '][label_color]">',
                    '      <option value="success">success</option>',
                    '      <option value="primary" selected>primary</option>',
                    '      <option value="info">info</option>',
                    '      <option value="warning">warning</option>',
                    '    </select>',
                    '  </div>',
                    '  <div class="col-md-1">',
                    '    <button type="button" class="btn btn-light-danger w-100 remove-package-benefit">Remover</button>',
                    '  </div>',
                    '</div>'
                ].join('');

                benefitsContainer.append(rowHtml);
            }

            $(document).on('click', '.package-module-card', function () {
                const moduleId = Number($(this).data('module-id'));
                toggleModule(moduleId);
            });

            $(document).on('change', '.package-module-tier-select', function () {
                const moduleId = Number($(this).data('module-id'));
                const tierId = Number($(this).val() || 0);

                selectedModules = selectedModules.map((item) => {
                    if (Number(item.module_id) !== moduleId) return item;
                    return {
                        ...item,
                        module_pricing_tier_id: tierId,
                    };
                });

                renderSelectedModules();
            });

            addBenefitButton.on('click', function () {
                addBenefitRow();
            });

            $(document).on('click', '.remove-package-benefit', function () {
                $(this).closest('.package-benefit-row').remove();
            });

            syncCardsVisual();
            renderSelectedModules();
        });
    </script>
@endsection
