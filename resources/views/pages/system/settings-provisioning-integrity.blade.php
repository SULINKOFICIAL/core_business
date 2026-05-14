@extends('layouts.app')

@section('title', 'Integridade do Provisionamento')

@section('content')
<div class="row g-6">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Integridade do Provisionamento</h3>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('system.settings.provisioning.integrity') }}" class="btn btn-sm btn-light-primary">
                        <i class="fa-solid fa-rotate-right me-1"></i>
                        Verificar novamente
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($integrity['ready'])
                    <div class="alert alert-success d-flex align-items-start p-5 mb-6">
                        <i class="fa-solid fa-circle-check fs-2hx text-success me-4 mt-1"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 mb-1">Ambiente pronto</span>
                            <span class="text-gray-700">
                                Os requisitos principais para criar novos MiCores foram validados.
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger d-flex align-items-start p-5 mb-6">
                        <i class="fa-solid fa-triangle-exclamation fs-2hx text-danger me-4 mt-1"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 mb-1">Ambiente incompleto</span>
                            <span class="text-gray-700">
                                Um ou mais requisitos falharam. Corrija os itens abaixo antes de criar novos MiCores.
                            </span>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed gy-4">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Verificação</th>
                                <th>Status</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @foreach ($integrity['checks'] as $check)
                                <tr>
                                    <td class="text-gray-900">
                                        {{ $check['label'] }}
                                    </td>
                                    <td>
                                        @if ($check['status'] === 'success')
                                            <span class="badge badge-light-success">
                                                <i class="fa-solid fa-check text-success me-1"></i>
                                                OK
                                            </span>
                                        @else
                                            <span class="badge badge-light-danger">
                                                <i class="fa-solid fa-xmark text-danger me-1"></i>
                                                Falha
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $check['message'] }}</div>
                                        @if (!empty($check['detail']))
                                            <div class="text-gray-500 fs-7 mt-1">{{ $check['detail'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Resumo</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-gray-700">Verificações</span>
                    <span class="fw-bold text-gray-900">{{ $integrity['summary']['total'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-gray-700">Aprovadas</span>
                    <span class="fw-bold text-success">{{ $integrity['summary']['success'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <span class="text-gray-700">Com falha</span>
                    <span class="fw-bold text-danger">{{ $integrity['summary']['error'] }}</span>
                </div>
                <div class="separator separator-dashed border-gray-300 my-6"></div>
                <div class="text-gray-600 fs-7">
                    A verificação usa as credenciais atuais do <code>.env</code>, valida a API do cPanel, procura o banco template e testa a autenticação SSH usada na clonagem.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
