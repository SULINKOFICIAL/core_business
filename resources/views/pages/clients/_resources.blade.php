<div class="mt-2" style="column-count: 2; column-gap: 1.5rem">
    @foreach ($modulesByCategory as $categoryName => $categoryModules)
    <div style="break-inside: avoid;">
        <div class="card mb-4">
            <div class="card-header bg-dark d-flex align-items-center" style="min-height: 40px !important; height: 40px !important; padding: 0 0.75rem !important;">
                <h6 class="text-white mb-0 lh-1">{{ $categoryName }}</h6>
            </div>
            <div class="card-body p-0">
                @foreach ($categoryModules as $module)
                <div class="card">
                    <div class="card-header min-h-40px bg-secondary p-0 border-bottom border-secondary rounded-0">
                        <div class="card-title">
                            <span class="card-icon">
                                <i class="flaticon2-line-chart text-primary"></i>
                            </span>
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer input-modules" type="checkbox" value="{{ $module->name }}" data-category="{{ $categoryName }}" id="module_{{ $module->name }}" @if(isset($allowModules[$module->name]) && $allowModules[$module->name] == true) checked @endif>
                                <label class="form-check-label d-flex align-items-center" for="module_{{ $module->name }}">
                                    <h4 class="card-label text-gray-800 mb-0">
                                        {{ $module->name }}
                                    </h4>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4 py-2">
                        @if ($module->resources->count())
                            @foreach ($module->resources as $resource)
                            <div class="rounded mb-2 p-2">
                                <div class="d-flex flex-wrap gap-3">
                                    @if ($resource->count())
                                        <span class="fw-semibold text-gray-700 cursor-pointer">
                                            {{ $resource->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-primary d-flex align-items-center p-5 mb-0">
                                <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-primary">Sem Recursos</h4>
                                    <span>Nesse setor nao foi cadastrado nenhum recurso, <a href="{{ route('modules.edit', $module->id) }}" class="fw-bold text-hover-danger">clique aqui</a> para adicionar.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
