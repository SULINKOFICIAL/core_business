<div class="row">
    <div class="col-12 col-md-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome da empresa</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $content->name ?? old('name') }}" maxlength="255" required>
    </div>
    <div class="col-12 col-md-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Domínio</label>
        <input type="text" class="form-control form-control-solid" placeholder="www.dominio.com.br" name="domain" value="{{ $content->domain ?? old('domain') }}" maxlength="255" required>
    </div>
    <div class="col-12 col-md-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Logo</label>
        <input type="file" name="logo" class="form-control form-control-solid">
    </div>
</div>
