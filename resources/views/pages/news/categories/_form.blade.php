<div class="row">
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $news->name ?? old('name') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Cor</label>
        <input type="color" class="form-control form-control-solid h-50px" placeholder="cor" name="color" value="{{ $news->color ?? old('color') }}" required>
    </div>
</div>
