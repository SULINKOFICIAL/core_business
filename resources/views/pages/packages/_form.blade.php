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
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Módulos Inclusos</label>
                <select
                    class="form-select form-select-solid"
                    id="package-modules-select"
                    data-control="select2"
                    data-placeholder="Selecionar"
                    multiple
                >
                    @foreach ($modules as $module)
                        <option value="{{ (int) $module->id }}" @selected(in_array((int) $module->id, $selectedModuleIds, true))>
                            {{ $module->name }}
                        </option>
                    @endforeach
                </select>
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

<div id="module-items-inputs"></div>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Preços dos módulos no pacote</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-dashed align-middle gs-0 gy-2" id="selected-modules-table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-7">
                        <th class="min-w-250px">Módulo</th>
                        <th class="min-w-180px">Tier/Faixa</th>
                        <th class="text-end min-w-150px">Preço do módulo</th>
                        <th class="text-end min-w-200px">Preço no pacote</th>
                    </tr>
                </thead>
                <tbody id="selected-modules-summary"></tbody>
                <tfoot>
                    <tr class="fw-bolder">
                        <td colspan="2" class="text-end text-gray-700">Total</td>
                        <td class="text-end">
                            <span class="text-muted me-2">Preço total</span>
                            <span class="text-success" id="selected-modules-total">R$ 0,00</span>
                        </td>
                        <td class="text-end">
                            <label class="form-label fs-8 fw-bold text-gray-700 mb-1 d-block">Preço do pacote</label>
                            <span class="text-primary fw-bolder" id="selected-modules-package-price">R$ 0,00</span>
                            <input type="hidden" name="value" id="selected-modules-package-price-input" value="{{ old('value', $package->value ?? 0) }}">
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="selected-modules-empty" class="text-muted fs-7">Nenhum módulo selecionado.</div>
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
                    <button type="button" class="btn btn-light-danger w-100 remove-package-benefit" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
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
            const totalCell = $('#selected-modules-total');
            const packagePriceCell = $('#selected-modules-package-price');
            const packagePriceInput = $('#selected-modules-package-price-input');
            const moduleItemsInputs = $('#module-items-inputs');
            const moduleSelect = $('#package-modules-select');

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

            function parseMoneyValue(rawValue) {
                if (rawValue && typeof rawValue === 'object' && rawValue.inputmask && typeof rawValue.inputmask.unmaskedvalue === 'function') {
                    const unmasked = String(rawValue.inputmask.unmaskedvalue() || '');
                    if (unmasked === '') return 0;

                    const cents = Number(unmasked);
                    return Number.isFinite(cents) ? (cents / 100) : 0;
                }

                const value = String(rawValue || '').trim();
                if (value === '') return 0;

                const normalized = value
                    .replace(/\s/g, '')
                    .replace(/[R$r$]/g, '')
                    .replace(/\./g, '')
                    .replace(',', '.')
                    .replace(/[^0-9.-]/g, '');

                const parsed = Number(normalized);
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function applyMoneyMaskToPackageInputs() {
                const selector = '#selected-modules-summary input[name^="prices["], #selected-modules-summary input[name^="tier_prices["]';
                if (typeof Inputmask === 'undefined') return;

                Inputmask(["R$ 9", "R$ 99", "R$ 9,99", "R$ 99,99", "R$ 999,99", "R$ 9.999,99", "R$ 99.999,99", "R$ 999.999,99", "R$ 9.999.999,99"], {
                    numericInput: true,
                    clearIncomplete: true,
                }).mask($(selector));
            }

            function updateSelectedModulesTotal() {
                let moduleTotal = 0;
                let packageTotal = 0;

                summaryBody.find('tr').each(function () {
                    const row = $(this);
                    const modulePrice = Number(row.data('current-price') || 0);
                    const packageInput = row.find('input[name^="prices["], input[name^="tier_prices["]').first();
                    const packagePrice = parseMoneyValue(packageInput.get(0) || packageInput.val());
                    moduleTotal += modulePrice;
                    packageTotal += packagePrice > 0 ? packagePrice : modulePrice;
                });

                totalCell.text(formatCurrency(moduleTotal));
                packagePriceCell.text(formatCurrency(packageTotal));
                packagePriceInput.val(packageTotal.toFixed(2));
            }

            function selectedMap() {
                const map = {};
                selectedModules.forEach((item) => {
                    map[Number(item.module_id)] = item;
                });
                return map;
            }

            function syncModuleSelectVisual() {
                const selectedIds = selectedModules.map((item) => String(Number(item.module_id)));
                moduleSelect.val(selectedIds).trigger('change.select2');
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

                    if (!module.is_usage) {
                        const fixedRowHtml = [
                            '<tr data-current-price="' + Number(module.value || 0) + '">',
                            '  <td class="fw-semibold text-gray-800">' + module.name + '</td>',
                            '  <td class="text-gray-700"><span class="badge badge-light-primary">Preço fixo</span></td>',
                            '  <td class="text-end text-gray-700">' + module.value_formatted + '</td>',
                            '  <td class="text-end">',
                            '    <input type="text" name="prices[' + module.id + ']" value="" class="form-control form-control-solid input-money text-end" placeholder="' + module.value_formatted + '">',
                            '  </td>',
                            '</tr>'
                        ].join('');

                        summaryBody.append(fixedRowHtml);
                        item.module_pricing_tier_id = 0;
                    } else {
                        const tiers = Array.isArray(module.tiers) ? module.tiers : [];
                        const fallbackTierId = tiers.length ? Number(tiers[0].id) : 0;
                        const selectedTierId = tiers.some((tier) => Number(tier.id) === Number(item.module_pricing_tier_id))
                            ? Number(item.module_pricing_tier_id)
                            : fallbackTierId;
                        item.module_pricing_tier_id = selectedTierId;

                        if (!tiers.length) {
                            const noTierRowHtml = [
                                '<tr data-current-price="0">',
                                '  <td class="fw-semibold text-gray-800">' + module.name + '</td>',
                                '  <td class="text-gray-700"><span class="badge badge-light-warning">Sem faixas</span></td>',
                                '  <td class="text-end text-gray-700"><span class="badge badge-light-warning">Sem faixas cadastradas</span></td>',
                                '  <td class="text-end"><input type="text" value="" class="form-control form-control-solid text-end" placeholder="Sem faixas para atualizar" disabled></td>',
                                '</tr>'
                            ].join('');

                            summaryBody.append(noTierRowHtml);
                        } else {
                            tiers.forEach((tier) => {
                                const usageRowHtml = [
                                    '<tr data-current-price="' + Number(tier.price || 0) + '">',
                                    '  <td class="fw-semibold text-gray-800">' + module.name + '</td>',
                                    '  <td class="text-gray-700"><span class="badge badge-light-success">Até ' + tier.usage_limit + '</span></td>',
                                    '  <td class="text-end text-gray-700">' + tier.price_formatted + '</td>',
                                    '  <td class="text-end">',
                                    '    <input type="text" name="tier_prices[' + tier.id + ']" value="" class="form-control form-control-solid input-money text-end" placeholder="' + tier.price_formatted + '">',
                                    '  </td>',
                                    '</tr>'
                                ].join('');

                                summaryBody.append(usageRowHtml);
                            });
                        }
                    }

                    moduleItemsInputs.append('<input type="hidden" name="module_items[' + index + '][module_id]" value="' + module.id + '">');
                    moduleItemsInputs.append('<input type="hidden" name="module_items[' + index + '][module_pricing_tier_id]" value="' + (item.module_pricing_tier_id || 0) + '">');
                });

                applyMoneyMaskToPackageInputs();
                updateSelectedModulesTotal();
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
                    '    <button type="button" class="btn btn-light-danger w-100 remove-package-benefit" title="Remover"><i class="fas fa-trash-alt"></i></button>',
                    '  </div>',
                    '</div>'
                ].join('');

                benefitsContainer.append(rowHtml);
            }

            moduleSelect.on('change', function () {
                const selectedIds = ($(this).val() || []).map((id) => Number(id)).filter((id) => id > 0);
                const currentMap = {};
                selectedModules.forEach((item) => {
                    currentMap[Number(item.module_id)] = item;
                });

                selectedModules = selectedIds.map((moduleId) => {
                    const existing = currentMap[moduleId];
                    if (existing) {
                        return existing;
                    }

                    const module = findModule(moduleId);
                    const defaultTierId = module && module.is_usage && module.tiers.length
                        ? Number(module.tiers[0].id)
                        : 0;

                    return {
                        module_id: moduleId,
                        module_pricing_tier_id: defaultTierId,
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

            $(document).on('input change keyup', '#selected-modules-summary input[name^="prices["], #selected-modules-summary input[name^="tier_prices["]', function () {
                updateSelectedModulesTotal();
            });

            renderSelectedModules();
            syncModuleSelectVisual();
        });
    </script>
@endsection
