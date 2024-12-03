<div class="row">
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $groups->name ?? old('name') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Recursos</label>
        <select name="resources[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" multiple>
            <option value=""></option>
            @foreach ($resources as $resource)
            <option value="{{ $resource->id }}" @if(isset($groups) && in_array($resource->id, $groups->resources->pluck('id')->toArray())) selected @endif>{{ $resource->name }}</option>
            @endforeach
        </select>
    </div>
</div>
