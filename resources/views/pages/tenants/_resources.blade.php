@php
    $totalEnabledModules = collect($enabledModules)->count();
    $totalModules = (int) ($totalModulesCount ?? $totalEnabledModules);
@endphp

<div class="card mb-4" id="tenant-enabled-modules-card">
    <div class="card-header border-bottom border-gray-200 py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between w-100 gap-3">
            <div>
                <h3 class="card-title fw-bolder text-gray-800 mb-1">Módulos habilitados</h3>
                <div class="text-gray-600 fs-6">
                    <span id="tenant-enabled-modules-counter">{{ $totalEnabledModules }}</span> de {{ $totalModules }} módulos ativos no sistema
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass fs-6 text-gray-400 position-absolute top-50 start-0 translate-middle-y ms-4"></i>
                    <input
                        type="text"
                        id="tenant-modules-search"
                        class="form-control form-control-solid ps-12 w-250px"
                        placeholder="Buscar módulo..."
                    >
                </div>

                <div class="btn-group" role="group" aria-label="Filtro módulos">
                    <button type="button" class="btn btn-sm btn-light-primary js-modules-filter active" data-filter="all">Todos</button>
                    <button type="button" class="btn btn-sm btn-light js-modules-filter" data-filter="enabled">Habilitados</button>
                    <button type="button" class="btn btn-sm btn-light js-modules-filter" data-filter="disabled">Indisponíveis</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        @foreach ($modulesByCategory as $categoryName => $categoryModules)
            @php
                $categoryTotal = $categoryModules->count();
            @endphp

            <div class="mb-8 tenant-category-block" data-category-name="{{ strtolower($categoryName) }}">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center w-100">
                        <p class="mb-0 fs-5 fw-bolder text-gray-800 text-uppercase me-3">{{ $categoryName }}</p>
                        <div class="separator border-gray-300 my-0"></div>
                    </div>
                    <span class="text-muted fs-7 text-nowrap ms-3 tenant-category-count" data-total="{{ $categoryTotal }}">{{ $categoryTotal }}/{{ $categoryTotal }}</span>
                </div>

                <div class="row g-3 tenant-category-grid">
                    @foreach ($categoryModules as $module)
                        <div class="col-12 col-md-6 col-xl-4 col-xxl-3 tenant-module-item" data-status="enabled" data-module-name="{{ strtolower($module->name) }}">
                            <div class="border border-gray-300 rounded px-3 py-2 h-100 bg-light d-flex align-items-center">
                                <span class="symbol symbol-28px me-3">
                                    <span class="symbol-label bg-light-success text-success">
                                        <i class="fa-solid fa-check fs-7"></i>
                                    </span>
                                </span>
                                <span class="fw-semibold text-gray-800">{{ $module->name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="alert alert-light-warning d-none mb-0" id="tenant-modules-empty-state">
            <i class="fa-solid fa-circle-info me-2"></i>Nenhum módulo encontrado com os filtros atuais.
        </div>
    </div>
</div>

@section('custom-footer')
    @parent
    <script>
        $(document).ready(function () {
            const card = $('#tenant-enabled-modules-card');
            if (!card.length) {
                return;
            }

            const searchInput = $('#tenant-modules-search');
            const filterButtons = card.find('.js-modules-filter');
            const moduleItems = card.find('.tenant-module-item');
            const categoryBlocks = card.find('.tenant-category-block');
            const totalCounter = $('#tenant-enabled-modules-counter');
            const emptyState = $('#tenant-modules-empty-state');

            let selectedFilter = 'all';

            function applyFilters() {
                const searchTerm = (searchInput.val() || '').toString().toLowerCase().trim();
                let visibleModules = 0;

                moduleItems.each(function () {
                    const item = $(this);
                    const moduleName = item.data('module-name') || '';
                    const moduleStatus = item.data('status') || 'enabled';

                    const matchesSearch = searchTerm === '' || moduleName.includes(searchTerm);
                    const matchesFilter = selectedFilter === 'all'
                        || (selectedFilter === 'enabled' && moduleStatus === 'enabled')
                        || (selectedFilter === 'disabled' && moduleStatus === 'disabled');

                    const shouldShow = matchesSearch && matchesFilter;
                    item.toggle(shouldShow);

                    if (shouldShow) {
                        visibleModules += 1;
                    }
                });

                categoryBlocks.each(function () {
                    const category = $(this);
                    const visibleInCategory = category.find('.tenant-module-item:visible').length;
                    const totalInCategory = Number(category.find('.tenant-category-count').data('total')) || 0;

                    category.toggle(visibleInCategory > 0);
                    category.find('.tenant-category-count').text(visibleInCategory + '/' + totalInCategory);
                });

                totalCounter.text(visibleModules);
                emptyState.toggleClass('d-none', visibleModules > 0);
            }

            searchInput.on('keyup', applyFilters);

            filterButtons.on('click', function () {
                filterButtons.removeClass('btn-light-primary active').addClass('btn-light');
                $(this).removeClass('btn-light').addClass('btn-light-primary active');
                selectedFilter = $(this).data('filter') || 'all';
                applyFilters();
            });

            applyFilters();
        });
    </script>
@endsection
