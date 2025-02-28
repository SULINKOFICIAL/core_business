<div class="row">
    <div class="col-10 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $packages->name ?? old('name') }}" required>
    </div>
    <div class="col-2 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Ordem</label>
        <input type="text" class="form-control form-control-solid" name="order" value="{{ $packages->order ?? old('order') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="value" value="R$ {{ number_format($packages->value, 2, ',', '.') ?? old('value') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Dias liberados</label>
        <input type="number" class="form-control form-control-solid" min="1" name="duration_days" value="{{ $packages->duration_days ?? old('duration_days') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Setores</label>
        <select name="groups[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple>
            <option value=""></option>
            @foreach ($groups as $group)
            <option value="{{ $group->id }}" @if(isset($sectors) && in_array($group->id, $sectors->groups->pluck('id')->toArray())) selected @endif>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
</div>
