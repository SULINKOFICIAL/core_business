<div class="row">
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Código</label>
        <input type="text" class="form-control form-control-solid" placeholder="EX: PROMO10" name="code" value="{{ $coupon->code ?? old('code') }}" required>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Tipo</label>
        <select name="type" id="coupon-type" class="form-select form-select-solid" data-control="select2" data-hide-search="true" required>
            <option value=""></option>
            <option value="percent" @if(isset($coupon) && $coupon->type === 'percent') selected @endif>Percentual</option>
            <option value="fixed" @if(isset($coupon) && $coupon->type === 'fixed') selected @endif>Valor fixo</option>
            <option value="trial" @if(isset($coupon) && $coupon->type === 'trial') selected @endif>Trial (meses grátis)</option>
        </select>
    </div>
    <div class="col-4 mb-4" id="coupon-amount-group">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Valor</label>
        <input type="text" class="form-control form-control-solid input-money" name="amount" value="{{ isset($coupon) && $coupon->amount ? 'R$ ' . number_format($coupon->amount, 2, ',', '.') : old('amount') }}" placeholder="Ex: 10 ou R$ 50,00">
        <span class="text-muted fs-8">Para percentual, informe o número (ex: 10). Para valor fixo, use reais.</span>
    </div>
    <div class="col-4 mb-4" id="coupon-trial-group">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Meses de trial</label>
        <input type="number" min="1" class="form-control form-control-solid" name="trial_months" value="{{ $coupon->trial_months ?? old('trial_months') }}" placeholder="Ex: 2">
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Máximo de usos</label>
        <input type="number" min="1" class="form-control form-control-solid" name="max_redemptions" value="{{ $coupon->max_redemptions ?? old('max_redemptions') }}" placeholder="Ex: 100">
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Status</label>
        <select name="is_active" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
            <option value="1" @if(!isset($coupon) || $coupon->is_active) selected @endif>Ativo</option>
            <option value="0" @if(isset($coupon) && !$coupon->is_active) selected @endif>Inativo</option>
        </select>
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Início da validade</label>
        <input type="datetime-local" class="form-control form-control-solid" name="starts_at" value="{{ isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\\TH:i') : old('starts_at') }}">
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Fim da validade</label>
        <input type="datetime-local" class="form-control form-control-solid" name="ends_at" value="{{ isset($coupon) && $coupon->ends_at ? $coupon->ends_at->format('Y-m-d\\TH:i') : old('ends_at') }}">
    </div>
</div>

<script>

    /**
     * Alterna os campos do formulário conforme o tipo de cupom.
     */
    function updateCouponFormVisibility() {

        const typeValue = String($('#coupon-type').val() || '');

        if (typeValue === 'trial') {
            // Trial usa meses, esconde valor
            $('#coupon-amount-group').addClass('d-none');
            $('#coupon-trial-group').removeClass('d-none');
        } else if (typeValue === 'percent' || typeValue === 'fixed') {
            // Percentual e fixo usam valor
            $('#coupon-amount-group').removeClass('d-none');
            $('#coupon-trial-group').addClass('d-none');
        } else {
            // Sem tipo selecionado, exibe tudo
            $('#coupon-amount-group').removeClass('d-none');
            $('#coupon-trial-group').removeClass('d-none');
        }

    }

    // Atualiza ao abrir o formulário
    updateCouponFormVisibility();

    // Atualiza quando o tipo mudar
    $(document).on('change', '#coupon-type', function() {
        updateCouponFormVisibility();
    });
</script>
