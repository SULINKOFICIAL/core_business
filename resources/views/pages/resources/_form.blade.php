<div class="row">
    <div class="mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $resources->name ?? old('name') }}" required>
    </div>
    <div class="mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Slug</label>
        <input type="text" class="form-control form-control-solid" name="slug" value="{{ $resources->slug ?? old('slug') }}" required>
    </div>
</div>
