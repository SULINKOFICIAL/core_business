@php
    $totalEnabledModules = collect($enabledModules)->count();
    $totalModules        = (int) ($totalModulesCount ?? $totalEnabledModules);
    $modules             = collect($modulesByCategory)->flatten(1)->unique('id')->values();
@endphp

<div class="card mb-4" id="tenant-enabled-modules-card">
    <div class="card-header border-bottom border-gray-200 py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between w-100 gap-3">
            <div>
                <h3 class="card-title fw-bolder text-gray-800 mb-1">Módulos habilitados</h3>
                <div class="text-gray-600 fs-6">
                    <span class="fw-bolder" id="tenant-enabled-modules-counter">{{ $totalEnabledModules }}</span> de {{ $totalModules }} módulos ativos no sistema
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light-primary active">Atualizar Plano do Cliente</button>
            </div>
        </div>
    </div>

    <div class="card-body">
        @if ($modules->isNotEmpty())
            <div class="row g-3">
                @foreach ($modules as $module)
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <div class="rounded px-3 py-2 h-100 bg-light d-flex align-items-center">
                            <span class="symbol symbol-30px me-3">
                                <span class="symbol-label bg-light-success text-success">
                                    <i class="fa-solid fa-check text-success fs-6"></i>
                                </span>
                            </span>
                            <span class="fw-semibold text-gray-800">{{ $module->name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light-warning mb-0">
                <i class="fa-solid fa-circle-info me-2"></i>Nenhum módulo habilitado para este cliente.
            </div>
        @endif
    </div>
</div>
