@php
    // Resolve tipo de cobrança e valores iniciais (old() tem prioridade)
    $pricingType = old('pricing_type', $modules->pricing_type ?? 'fixed');
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
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Grupos de Recursos</label>
                <select name="groups[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple>
                    <option value=""></option>
                    @foreach ($groups as $group)
                    <option value="{{ $group->id }}" @if(isset($modules) && in_array($group->id, $modules->groups->pluck('id')->toArray())) selected @endif>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Precificação</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Tipo de Cobrança</label>
                <select name="pricing_type" class="form-select form-select-solid" id="pricing_type">
                    <option value="fixed" @selected($pricingType === 'fixed')>Preço fixo</option>
                    <option value="usage" @selected($pricingType === 'usage')>Preço por uso</option>
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

@section('custom-footer')
    @parent
    <script>
        $(function () {
            // Cacheia elementos principais do formulário
            var pricingTypeSelect = $('#pricing_type');
            if (!pricingTypeSelect.length) return;

            var fixedBlocks = $('.pricing-fixed');
            var usageBlocks = $('.pricing-usage');
            var tiersContainer = $('#pricing-tiers');
            var addTierButton = $('#add-tier');
            var valueInput = $('input[name="value"]');

            function togglePricingBlocks() {
                // Alterna visibilidade entre preço fixo e por uso
                var isUsage = pricingTypeSelect.val() === 'usage';
                fixedBlocks.toggle(!isUsage);
                usageBlocks.toggle(isUsage);
                // Ajusta required do valor fixo
                if (valueInput.length) {
                    valueInput.prop('required', !isUsage);
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

            // Botão de adicionar faixa
            addTierButton.on('click', function () {
                addTierRow();
            });

            // Remove faixa clicando no botão "Remover"
            $(document).on('click', '.remove-tier', function () {
                $(this).closest('.pricing-tier-row').remove();
            });

            // Atualiza a UI quando muda o tipo de cobrança
            pricingTypeSelect.on('change', togglePricingBlocks);
            togglePricingBlocks();
        });
    </script>
@endsection
