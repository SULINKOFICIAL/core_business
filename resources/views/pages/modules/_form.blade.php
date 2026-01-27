@php
    $pricingType = old('pricing_type', $modules->pricing_type ?? 'fixed');
    $usageLabel = old('usage_label', $modules->usage_label ?? '');
    $tiers = old('tiers');

    if (is_array($tiers)) {
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
        $tiers = $modules->pricingTiers->map(function ($tier) {
            return [
                'limit' => $tier->usage_limit,
                'price' => number_format($tier->price, 2, ',', '.'),
            ];
        })->toArray();
    }

    if (empty($tiers)) {
        $tiers = [
            ['limit' => '', 'price' => ''],
        ];
    }
@endphp

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
    <div class="col-6 mb-4 pricing-fixed">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($modules->value ?? 0), 2, ',', '.') }}" required>
    </div>
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
