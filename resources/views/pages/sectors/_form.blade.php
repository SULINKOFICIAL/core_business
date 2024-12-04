<div class="row">
    <div class="mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $sectors->name ?? old('name') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Grupos de Recursos</label>
        <select name="groups[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple>
            <option value=""></option>
            @foreach ($groups as $group)
            <option value="{{ $group->id }}" @if(isset($sectors) && in_array($group->id, $sectors->groups->pluck('id')->toArray())) selected @endif>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
</div>
