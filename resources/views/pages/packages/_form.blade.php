<div class="row">
    <div class="col-12 col-md-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $packages->name ?? old('name') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Valor</label>
        <input type="text" class="form-control form-control-solid" name="value" value="{{ $packages->value ?? old('value') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Ordem</label>
        <input type="text" class="form-control form-control-solid" name="order" value="{{ $packages->order ?? old('order') }}" required>
    </div>
</div>
