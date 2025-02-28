<div class="row">
    @foreach ($modules as $module)
    <div class="col-6">
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="flaticon2-line-chart text-primary"></i>
                    </span>
                    <h3 class="card-label">
                        {{ $module->name }}
                    </h3>
                </div>
            </div>
            <div class="card-body">
                @if ($module->groups->count())
                    @foreach ($module->groups as $group)
                    <div class="rounded mb-4 p-4 bg-light">
                        <p class="text-capitalize mb-2 fw-bold text-gray-700">{{ $group->name }}</p>
                        <div class="d-flex flex-wrap gap-3">
                            @if ($group->resources->count())
                                @foreach ($group->resources as $item)
                                    <label class="form-check form-switch form-check-custom form-check-solid me-6">
                                        <input class="form-check-input cursor-pointer input-features" type="checkbox" value="{{ $item->name }}" @if(isset($allowFeatures[$item->name]) && $allowFeatures[$item->name] == true) checked @endif/>
                                        <span class="form-check-label fw-semibold text-gray-700 cursor-pointer">
                                            {{ $item->name }}
                                        </span>
                                    </label>
                                @endforeach
                            @else
                                <div class="alert alert-info d-flex align-items-center p-5 mb-0">
                                    <i class="ki-duotone ki-shield-tick fs-2hx text-info me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-info">Sem Recursos no Grupo</h4>
                                        <span>Não foram atribuidos recursos a esse grupo, <a href="{{ route('groups.edit', $group->id) }}" class="fw-bold text-hover-danger">clique aqui</a> para atribuir.</span>
                                    </div>
                                </div>
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
                        <h4 class="mb-1 text-primary">Sem Grupo de Recursos</h4>
                        <span>Nesse setor não foi cadastrado nenhum grupo de recursos, <a href="{{ route('modules.edit', $module->id) }}" class="fw-bold text-hover-danger">clique aqui</a> para adicionar.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>