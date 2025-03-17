<div class="row">
    <div class="col-8 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $package->name ?? old('name') }}" required>
    </div>
    <div class="col-2 mb-4">
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
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($package->value ?? 0), 2, ',', '.') }}" required>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Dias liberados</label>
        <input type="number" class="form-control form-control-solid" min="1" name="duration_days" value="{{ $package->duration_days ?? 30 }}" required>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Espaço</label>
        <select name="size_storage" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Selecione" required>
            <option value=""></option>
            <option value="5368709120" @if(!isset($package) || $package->size_storage == 5368709120) selected @endif>5gb</option>
            <option value="10737418240" @if(isset($package) && $package->size_storage == 10737418240) selected @endif>10gb</option>
            <option value="16106127360" @if(isset($package) && $package->size_storage == 16106127360) selected @endif>15gb</option>
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
