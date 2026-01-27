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

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
                <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $modules->name ?? old('name') }}" required>
            </div>
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
            <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Descrição</label>
                <textarea maxlength="170" class="form-control form-control-solid" name="description" required>{{ $modules->description ?? old('description') }}</textarea>
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

@section('custom-footer')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var pricingTypeSelect = document.getElementById('pricing_type');
            if (!pricingTypeSelect) return;

            var fixedBlocks = document.querySelectorAll('.pricing-fixed');
            var usageBlocks = document.querySelectorAll('.pricing-usage');
            var tiersContainer = document.getElementById('pricing-tiers');
            var addTierButton = document.getElementById('add-tier');
            var valueInput = document.querySelector('input[name="value"]');

            function togglePricingBlocks() {
                var isUsage = pricingTypeSelect.value === 'usage';
                fixedBlocks.forEach(function (el) {
                    el.style.display = isUsage ? 'none' : '';
                });
                usageBlocks.forEach(function (el) {
                    el.style.display = isUsage ? '' : 'none';
                });
                if (valueInput) {
                    valueInput.required = !isUsage;
                }
            }

            function nextTierIndex() {
                var items = tiersContainer ? tiersContainer.querySelectorAll('.pricing-tier-row') : [];
                return items.length;
            }

            function addTierRow() {
                if (!tiersContainer) return;
                var index = nextTierIndex();
                var row = document.createElement('div');
                row.className = 'row align-items-end pricing-tier-row mb-3';
                row.innerHTML = [
                    '<div class="col-5">',
                    '  <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Até</label>',
                    '  <input type="text" class="form-control form-control-solid" name="tiers[' + index + '][limit]" value="">',
                    '</div>',
                    '<div class="col-5">',
                    '  <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Preço</label>',
                    '  <input type="text" class="form-control form-control-solid input-money" name="tiers[' + index + '][price]" value="">',
                    '</div>',
                    '<div class="col-2">',
                    '  <button type="button" class="btn btn-light-danger w-100 remove-tier">Remover</button>',
                    '</div>',
                ].join('');

                tiersContainer.appendChild(row);
                if (typeof window.generateMasks === 'function') {
                    window.generateMasks();
                }
            }

            if (addTierButton) {
                addTierButton.addEventListener('click', function () {
                    addTierRow();
                });
            }

            document.addEventListener('click', function (event) {
                if (!event.target.classList.contains('remove-tier')) return;
                var row = event.target.closest('.pricing-tier-row');
                if (row) row.remove();
            });

            pricingTypeSelect.addEventListener('change', togglePricingBlocks);
            togglePricingBlocks();
        });
    </script>
@endsection