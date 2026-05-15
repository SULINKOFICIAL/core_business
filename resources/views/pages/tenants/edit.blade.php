@extends('layouts.app')

@section('title', 'Editar - Cliente')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Cliente
</p>
<form action="{{ route('tenants.update', $content->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.tenants._form')
        </div>
    </div>
    <div class="card mt-6">
        <div class="card-header align-items-center">
            <div class="d-flex flex-column">
                <span class="fw-bolder text-gray-800 fs-5">Provisionamento do MiCore</span>
                <span class="text-gray-600 fs-7">
                    Transforma o cadastro central em uma instalação operacional com domínio, banco, usuário inicial, token e módulos sincronizados.
                </span>
            </div>
            <div class="card-toolbar">
                <span class="badge {{ $provisioningStatus['class'] }}">
                    {{ $provisioningStatus['label'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            @if ($provisioning)
                <div class="row g-4 mb-5">
                    <div class="col-12 col-md-4">
                        <div class="text-gray-500 fs-8 fw-semibold text-uppercase mb-1">Próxima etapa</div>
                        <div class="fw-bolder text-gray-800">{{ $provisioningStatus['next'] }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-gray-500 fs-8 fw-semibold text-uppercase mb-1">Domínio principal</div>
                        <div class="fw-bolder text-gray-800">{{ $primaryDomain ?? 'Não informado' }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-gray-500 fs-8 fw-semibold text-uppercase mb-1">Banco do tenant</div>
                        <div class="fw-bolder text-gray-800">{{ $provisioning->table ?? 'Não informado' }}</div>
                    </div>
                </div>

                <div class="alert alert-light-primary d-flex align-items-start p-5 mb-5">
                    <i class="fa-solid fa-circle-info fs-2 text-primary me-4 mt-1"></i>
                    <div class="text-gray-700">
                        {{ $provisioningStatus['description'] }}
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if ($provisioningCompleted)
                        <a href="{{ route('tenants.show', $content->id) }}" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir cliente
                        </a>
                    @else
                        <a href="{{ route('tenants.install.index', $content->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-play me-1"></i>Continuar instalação
                        </a>
                    @endif
                    <a href="{{ route('system.settings.provisioning.integrity') }}" class="btn btn-sm btn-light-primary">
                        <i class="fa-solid fa-shield-halved me-1"></i>Integridade do Provisionamento
                    </a>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-start p-5 mb-5">
                    <i class="fa-solid fa-triangle-exclamation fs-2 text-warning me-4 mt-1"></i>
                    <div class="text-gray-700">
                        Este cliente não possui dados técnicos de provisionamento. Sem esse registro não há banco, usuário MySQL ou primeira conta para executar a instalação.
                    </div>
                </div>
                <a href="{{ route('system.settings.provisioning.integrity') }}" class="btn btn-sm btn-light-primary">
                    <i class="fa-solid fa-shield-halved me-1"></i>Integridade do Provisionamento
                </a>
            @endif
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('tenants.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-active-danger">
            Atualizar
        </button>
    </div>
</form>
@endsection
