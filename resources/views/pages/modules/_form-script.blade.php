<script>
    document.addEventListener('DOMContentLoaded', function () {
        var pricingTypeSelect = document.getElementById('pricing_type');
        if (!pricingTypeSelect) return;

        var fixedBlocks = document.querySelectorAll('.pricing-fixed');
        var usageBlocks = document.querySelectorAll('.pricing-usage');
        var tiersContainer = document.getElementById('pricing-tiers');
        var addTierButton = document.getElementById('add-tier');
        var valueInput = document.querySelector('input[name="value"]');

        function togglePricingBlocks() {
            var isUsage = pricingTypeSelect.value === 'usage';
            fixedBlocks.forEach(function (el) {
                el.style.display = isUsage ? 'none' : '';
            });
            usageBlocks.forEach(function (el) {
                el.style.display = isUsage ? '' : 'none';
            });
            if (valueInput) {
                valueInput.required = !isUsage;
            }
        }

        function nextTierIndex() {
            var items = tiersContainer ? tiersContainer.querySelectorAll('.pricing-tier-row') : [];
            return items.length;
        }

        function addTierRow() {
            if (!tiersContainer) return;
            var index = nextTierIndex();
            var row = document.createElement('div');
            row.className = 'row align-items-end pricing-tier-row mb-3';
            row.innerHTML = [
                '<div class="col-5">',
                '  <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Até</label>',
                '  <input type="text" class="form-control form-control-solid" name="tiers[' + index + '][limit]" value="">',
                '</div>',
                '<div class="col-5">',
                '  <label class="form-label fs-7 fw-bold text-gray-600 mb-1">Preço</label>',
                '  <input type="text" class="form-control form-control-solid input-money" name="tiers[' + index + '][price]" value="">',
                '</div>',
                '<div class="col-2">',
                '  <button type="button" class="btn btn-light-danger w-100 remove-tier">Remover</button>',
                '</div>',
            ].join('');

            tiersContainer.appendChild(row);
            if (typeof window.generateMasks === 'function') {
                window.generateMasks();
            }
        }

        if (addTierButton) {
            addTierButton.addEventListener('click', function () {
                addTierRow();
            });
        }

        document.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-tier')) return;
            var row = event.target.closest('.pricing-tier-row');
            if (row) row.remove();
        });

        pricingTypeSelect.addEventListener('change', togglePricingBlocks);
        togglePricingBlocks();
    });
</script>
