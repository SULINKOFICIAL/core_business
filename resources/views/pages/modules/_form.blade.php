@php
    // Resolve tipo de cobrança e valores iniciais (old() tem prioridade)
    $pricingType = old('pricing_type', $modules->pricing_type ?? 'Preço Fixo');
    $usageLabel = old('usage_label', $modules->usage_label ?? '');
    $tiers = old('tiers');

    if (is_array($tiers)) {
        // Normaliza preço das faixas quando a validação retorna old()
        $tiers = array_map(function ($tier) {
            $price = $tier['price'] ?? '';
            $price = preg_replace('/[^\d,\.]/', '', (string) $price);
            return [
                'limit' => $tier['limit'] ?? '',
                'price' => $price,
            ];
        }, $tiers);
    }

    if ($tiers === null && isset($modules)) {
        // Carrega faixas do banco ao editar
        $tiers = $modules->pricingTiers->map(function ($tier) {
            return [
                'limit' => $tier->usage_limit,
                'price' => number_format($tier->price, 2, ',', '.'),
            ];
        })->toArray();
    }

    if (empty($tiers)) {
        // Garante pelo menos uma linha no formulário
        $tiers = [
            ['limit' => '', 'price' => ''],
        ];
    }

    $benefits = old('benefits');
    $isNative = (bool) old('is_native', $modules->is_native ?? false);

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

    if ($benefits === null && isset($modules)) {
        $benefits = $modules->benefits->map(function ($benefit) {
            return [
                'icon' => $benefit->icon,
                'title' => $benefit->title,
                'label' => $benefit->label,
                'label_color' => $benefit->label_color,
            ];
        })->toArray();
    }

    if (empty($benefits)) {
        $benefits = [
            [
                'icon' => 'shop',
                'title' => 'Vendas e Pedidos',
                'label' => 'Ilimitado',
                'label_color' => 'primary',
            ],
        ];
    }
@endphp

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Informações gerais</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
                <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $modules->name ?? old('name') }}" required>
            </div>
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Categoria</label>
                <select name="module_category_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione">
                    <option value=""></option>
                    @foreach ($categories ?? [] as $category)
                    <option value="{{ $category->id }}" @if(old('module_category_id', $modules->module_category_id ?? null) == $category->id) selected @endif>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <label class="form-label fs-6 fw-bold text-gray-700 mb-0">Módulo Nativo</label>
                    <i
                        class="fa-solid fa-circle-info text-info cursor-pointer"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="Módulo incluso sem cobrança, usado para funcionamento do sistema."
                    ></i>
                </div>
                <select name="is_native" class="form-select form-select-solid" id="is_native">
                    <option value="0" @selected(!$isNative)>Não</option>
                    <option value="1" @selected($isNative)>Sim</option>
                </select>
            </div>
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Descrição</label>
                <textarea maxlength="170" class="form-control form-control-solid" name="description" required>{{ $modules->description ?? old('description') }}</textarea>
            </div>
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Capa do módulo</label>
                <input type="file" class="form-control form-control-solid" name="cover_image" accept="image/*">
                @if (isset($modules) && !empty($modules->cover_image))
                    <div class="mt-3">
                        <img src="{{ asset('storage/modules/' . $modules->id . '/' . $modules->cover_image) }}" alt="Capa do módulo" class="img-fluid rounded" style="max-height: 160px;">
                    </div>
                @endif
            </div>
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Recursos</label>
                <select name="resources[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple>
                    <option value=""></option>
                    @foreach ($resources as $resource)
                    <option value="{{ $resource->id }}" @if(isset($modules) && in_array($resource->id, $modules->resources->pluck('id')->toArray())) selected @endif>{{ $resource->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card" id="module-pricing-card">
    <div class="card-header">
        <h3 class="card-title">Precificação</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Tipo de Cobrança</label>
                <select name="pricing_type" class="form-select form-select-solid" id="pricing_type">
                    <option value="Preço Fixo" @selected($pricingType === 'Preço Fixo')>Preço Fixo</option>
                    <option value="Preço Por Uso" @selected($pricingType === 'Preço Por Uso')>Preço Por Uso</option>
                </select>
            </div>
            {{-- Bloco visível apenas para cobrança fixa (controlado via JS) --}}
            <div class="col-6 mb-4 pricing-fixed">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
                <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($modules->value ?? 0), 2, ',', '.') }}" required>
            </div>
            {{-- Bloco visível apenas para cobrança por uso (controlado via JS) --}}
            <div class="col-6 mb-4 pricing-usage">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Unidade de uso</label>
                <input type="text" class="form-control form-control-solid" placeholder="Ex: conversas/dia" name="usage_label" value="{{ $usageLabel }}">
            </div>
            <div class="col-12 mb-4 pricing-usage">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Faixas de cobrança</label>
                <div id="pricing-tiers">
                    @foreach ($tiers as $index => $tier)
                    {{-- Linha de faixa dinâmica (adicionar/remover via JS) --}}
                    <div class="row align-items-end pricing-tier-row mb-3">
                        <div class="col-5">
                            <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Até</label>
                            <input type="text" class="form-control form-control-solid" name="tiers[{{ $index }}][limit]" value="{{ $tier['limit'] }}">
                        </div>
                        <div class="col-5">
                            <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Preço</label>
                            <input type="text" class="form-control form-control-solid input-money" name="tiers[{{ $index }}][price]" value="R$ {{ $tier['price'] }}">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-light-danger w-100 remove-tier">Remover</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-light-primary mt-2" id="add-tier">Adicionar faixa</button>
                <div class="form-text text-gray-500">Exemplo: Até 30 conversas/dia → R$ 50,00</div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <h3 class="card-title">Benefícios</h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <p class="text-gray-600 mb-0">Itens exibidos abaixo do botão "Incluir no Plano".</p>
            <button type="button" class="btn btn-light-primary" id="add-benefit">Adicionar benefício</button>
        </div>
        <div id="module-benefits">
            @foreach ($benefits as $index => $benefit)
            <div class="row align-items-end module-benefit-row mb-3 border border-gray-200 rounded p-4">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Ícone</label>
                    <input type="text" class="form-control form-control-solid" name="benefits[{{ $index }}][icon]" placeholder="Ex: shop ou fa-solid fa-shop" value="{{ $benefit['icon'] }}">
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
                    <button type="button" class="btn btn-light-danger w-100 remove-benefit">Remover</button>
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
            // Cacheia elementos principais do formulário
            var pricingTypeSelect = $('#pricing_type');
            var nativeSelect = $('#is_native');
            var pricingCard = $('#module-pricing-card');
            if (!pricingTypeSelect.length) return;

            var fixedBlocks = $('.pricing-fixed');
            var usageBlocks = $('.pricing-usage');
            var tiersContainer = $('#pricing-tiers');
            var addTierButton = $('#add-tier');
            var valueInput = $('input[name="value"]');
            var benefitsContainer = $('#module-benefits');
            var addBenefitButton = $('#add-benefit');

            function togglePricingBlocks() {
                // Alterna visibilidade entre Preço Fixo e Preço Por Uso
                var isUsage = pricingTypeSelect.val() === 'Preço Por Uso';
                fixedBlocks.toggle(!isUsage);
                usageBlocks.toggle(isUsage);
                // Ajusta required do valor fixo
                if (valueInput.length) {
                    valueInput.prop('required', !isUsage);
                }
            }

            function togglePricingCard() {
                // Módulo nativo não exibe bloco de precificação.
                var isNative = nativeSelect.val() === '1';
                pricingCard.toggle(!isNative);

                if (isNative && valueInput.length) {
                    valueInput.prop('required', false);
                } else {
                    togglePricingBlocks();
                }
            }

            function nextTierIndex() {
                // Calcula o próximo índice com base nas linhas existentes
                return tiersContainer.length ? tiersContainer.find('.pricing-tier-row').length : 0;
            }

            function addTierRow() {
                // Adiciona uma nova linha de faixa de preço
                if (!tiersContainer.length) return;
                var index = nextTierIndex();
                var rowHtml = [
                    '<div class="row align-items-end pricing-tier-row mb-3">',
                    '  <div class="col-5">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Até</label>',
                    '    <input type="text" class="form-control form-control-solid" name="tiers[' + index + '][limit]" value="">',
                    '  </div>',
                    '  <div class="col-5">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Preço</label>',
                    '    <input type="text" class="form-control form-control-solid input-money" name="tiers[' + index + '][price]" value="">',
                    '  </div>',
                    '  <div class="col-2">',
                    '    <button type="button" class="btn btn-light-danger w-100 remove-tier">Remover</button>',
                    '  </div>',
                    '</div>',
                ].join('');

                tiersContainer.append(rowHtml);
                // Reaplica máscara monetária nos novos campos
                if (typeof window.generateMasks === 'function') {
                    window.generateMasks();
                }
            }

            function nextBenefitIndex() {
                return benefitsContainer.length ? benefitsContainer.find('.module-benefit-row').length : 0;
            }

            function addBenefitRow() {
                if (!benefitsContainer.length) return;
                var index = nextBenefitIndex();
                var rowHtml = [
                    '<div class="row align-items-end module-benefit-row mb-3 border border-gray-200 rounded p-4">',
                    '  <div class="col-md-3 mb-3 mb-md-0">',
                    '    <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Ícone</label>',
                    '    <input type="text" class="form-control form-control-solid" name="benefits[' + index + '][icon]" placeholder="Ex: shop ou fa-solid fa-shop" value="">',
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
                    '    <button type="button" class="btn btn-light-danger w-100 remove-benefit">Remover</button>',
                    '  </div>',
                    '</div>',
                ].join('');

                benefitsContainer.append(rowHtml);
            }

            // Botão de adicionar faixa
            addTierButton.on('click', function () {
                addTierRow();
            });
            addBenefitButton.on('click', function () {
                addBenefitRow();
            });

            // Remove faixa clicando no botão "Remover"
            $(document).on('click', '.remove-tier', function () {
                $(this).closest('.pricing-tier-row').remove();
            });
            $(document).on('click', '.remove-benefit', function () {
                $(this).closest('.module-benefit-row').remove();
            });

            // Atualiza a UI quando muda o tipo de cobrança
            pricingTypeSelect.on('change', togglePricingBlocks);
            nativeSelect.on('change', togglePricingCard);
            togglePricingCard();
        });
    </script>
@endsection
