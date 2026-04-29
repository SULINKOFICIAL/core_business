<div class="row">
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Quantidade de armazenamento</label>
        <input type="number" min="1" class="form-control form-control-solid" name="quantity" value="{{ $item->quantity ?? old('quantity') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Preço</label>
        <input type="text" class="form-control form-control-solid input-money" name="price" value="{{ isset($item) ? 'R$ ' . number_format((float) $item->price, 2, ',', '.') : old('price') }}" required>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Status</label>
        <select name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" required>
            <option value="1" @selected((int) old('status', $item->status ?? 1) === 1)>Ativo</option>
            <option value="0" @selected((int) old('status', $item->status ?? 1) === 0)>Inativo</option>
        </select>
    </div>
</div>
