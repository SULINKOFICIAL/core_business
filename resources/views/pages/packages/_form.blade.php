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
@endphp

<div class="row">
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $package->name ?? old('name') }}" required>
    </div>
    <div class="col-3 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($package->value ?? 0), 2, ',', '.') }}" required>
    </div>
    <div class="col-1 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Teste grátis?</label>
        <select name="free" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Selecione" required>
            <option value=""></option>
            <option value="0" @if(!isset($package) || $package->free == false) selected @endif>Não</option>
            <option value="1" @if(isset($package) && $package->free == true) selected @endif>Sim</option>
        </select>
    </div>
    <div class="col-2 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Ordem</label>
        <input type="text" class="form-control form-control-solid" name="order" value="{{ $package->order ?? 1 }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Descrição</label>
        <input type="text" class="form-control form-control-solid" placeholder="Descrição" name="description" value="{{ $package->description ?? old('description') }}">
    </div>
    <div class="col-3 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Dias liberados</label>
        <input type="number" class="form-control form-control-solid" min="1" name="duration_days" value="{{ $package->duration_days ?? 30 }}" required>
    </div>
    <div class="col-3 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Espaço</label>
        <select name="size_storage" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Selecione" required>
            <option value=""></option>
            <option value="1073741824" @if(isset($package) && $package->size_storage == 1073741824) selected @endif>1GB</option>
            <option value="2684354560" @if(isset($package) && $package->size_storage == 2684354560) selected @endif>2.5GB</option>
            <option value="5368709120" @if(!isset($package) || $package->size_storage == 5368709120) selected @endif>5GB</option>
            <option value="10737418240" @if(isset($package) && $package->size_storage == 10737418240) selected @endif>10GB</option>
            <option value="16106127360" @if(isset($package) && $package->size_storage == 16106127360) selected @endif>15GB</option>
            <option value="21474836480" @if(isset($package) && $package->size_storage == 21474836480) selected @endif>20GB</option>
            <option value="26843545600" @if(isset($package) && $package->size_storage == 26843545600) selected @endif>25GB</option>
            <option value="32212254720" @if(isset($package) && $package->size_storage == 32212254720) selected @endif>30GB</option>
            <option value="37580963840" @if(isset($package) && $package->size_storage == 37580963840) selected @endif>35GB</option>
            <option value="42949672960" @if(isset($package) && $package->size_storage == 42949672960) selected @endif>40GB</option>
            <option value="48318382080" @if(isset($package) && $package->size_storage == 48318382080) selected @endif>45GB</option>
            <option value="53687091200" @if(isset($package) && $package->size_storage == 53687091200) selected @endif>50GB</option>
        </select>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Modulos</label>
        <select name="modules[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple required>
            <option value=""></option>
            @foreach ($modules as $module)
            <option value="{{ $module->id }}" @if(isset($package) && in_array($module->id, $package->modules->pluck('id')->toArray())) selected @endif>{{ $module->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="separator my-6"></div>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1">Benefícios</h3>
        <p class="text-gray-600 mb-0">Itens exibidos no card do pacote.</p>
    </div>
    <button type="button" class="btn btn-light-primary" id="add-package-benefit">Adicionar benefício</button>
</div>

<div id="package-benefits">
    @foreach ($benefits as $index => $benefit)
    <div class="row align-items-end package-benefit-row mb-3 border border-gray-200 rounded p-4">
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
            <button type="button" class="btn btn-light-danger w-100 remove-package-benefit">Remover</button>
        </div>
    </div>
    @endforeach
</div>

@section('custom-footer')
    @parent
    <script>
        $(function () {
            var benefitsContainer = $('#package-benefits');
            var addBenefitButton = $('#add-package-benefit');

            function nextBenefitIndex() {
                return benefitsContainer.length ? benefitsContainer.find('.package-benefit-row').length : 0;
            }

            function addBenefitRow() {
                if (!benefitsContainer.length) return;
                var index = nextBenefitIndex();
                var rowHtml = [
                    '<div class="row align-items-end package-benefit-row mb-3 border border-gray-200 rounded p-4">',
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
                    '    <button type="button" class="btn btn-light-danger w-100 remove-package-benefit">Remover</button>',
                    '  </div>',
                    '</div>',
                ].join('');

                benefitsContainer.append(rowHtml);
            }

            addBenefitButton.on('click', function () {
                addBenefitRow();
            });

            $(document).on('click', '.remove-package-benefit', function () {
                $(this).closest('.package-benefit-row').remove();
            });
        });
    </script>
@endsection
