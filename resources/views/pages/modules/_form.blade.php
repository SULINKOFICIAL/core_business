<div class="row">
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $modules->name ?? old('name') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor no Upgrade</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format(($packages->value ?? 0), 2, ',', '.') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Descrição</label>
        <input type="text" maxlength="170" class="form-control form-control-solid" name="description" value="{{ $modules->description ?? old('description') }}" required>
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
