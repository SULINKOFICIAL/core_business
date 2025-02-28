<div class="row">
    <div class="col-10 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $packages->name ?? old('name') }}" required>
    </div>
    <div class="col-2 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Ordem</label>
        <input type="text" class="form-control form-control-solid" name="order" value="{{ $packages->order ?? 1 }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($packages->value ?? 0), 2, ',', '.') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Dias liberados</label>
        <input type="number" class="form-control form-control-solid" min="1" name="duration_days" value="{{ $packages->duration_days ?? 30 }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Modulos</label>
        <select name="modules[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple required>
            <option value=""></option>
            @foreach ($modules as $module)
            <option value="{{ $module->id }}" @if(isset($packages) && in_array($module->id, $packages->modules->pluck('id')->toArray())) selected @endif>{{ $module->name }}</option>
            @endforeach
        </select>
    </div>
</div>
