@extends('layouts.app')

@section('title', 'Consulta de Domínio cPanel')

@section('content')
<div class="row g-6">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Consulta de Domínio cPanel</h3>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('system.settings.provisioning.domain.lookup') }}">
                    <div class="row g-4 align-items-end">
                        <div class="col-12 col-lg-9">
                            <label class="form-label fw-semibold">Domínio</label>
                            <input
                                type="text"
                                name="domain"
                                class="form-control form-control-solid"
                                value="{{ old('domain', $domain) }}"
                                placeholder="teste-provisionamento.micore.com.br"
                                required
                            >
                            @error('domain')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-12 col-lg-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>
                                Consultar
                            </button>
                        </div>
                    </div>
                </form>

                @if ($lookup)
                    <div class="separator separator-dashed border-gray-300 my-6"></div>

                    @if ($lookup['error'])
                        <div class="alert alert-danger d-flex align-items-start p-5 mb-0">
                            <i class="fa-solid fa-triangle-exclamation fs-2hx text-danger me-4 mt-1"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-gray-800 mb-1">Falha na consulta</span>
                                <span class="text-gray-700">{{ $lookup['error'] }}</span>
                            </div>
                        </div>
                    @elseif ($lookup['exists'])
                        <div class="alert alert-success d-flex align-items-start p-5 mb-6">
                            <i class="fa-solid fa-circle-check fs-2hx text-success me-4 mt-1"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-gray-800 mb-1">Domínio encontrado no cPanel</span>
                                <span class="text-gray-700">{{ $lookup['domain'] }}</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-4">
                                <tbody class="fw-semibold text-gray-700">
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">Fonte</td>
                                        <td class="text-gray-900">{{ $lookup['source'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">Tipo</td>
                                        <td class="text-gray-900">{{ $lookup['details']['type'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">Document Root</td>
                                        <td class="text-gray-900">{{ $lookup['details']['documentroot'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">Status</td>
                                        <td class="text-gray-900">{{ $lookup['details']['status'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">IP</td>
                                        <td class="text-gray-900">{{ $lookup['details']['ip'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-500 text-uppercase fs-7">Aliases</td>
                                        <td class="text-gray-900">{{ $lookup['details']['serveralias'] ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-start p-5 mb-0">
                            <i class="fa-solid fa-circle-exclamation fs-2hx text-warning me-4 mt-1"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-gray-800 mb-1">Domínio não encontrado no cPanel</span>
                                <span class="text-gray-700">{{ $lookup['domain'] }}</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Como a consulta funciona</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="text-gray-700 mb-4">
                    A consulta usa as credenciais atuais do <code>.env</code> do core_business e chama a UAPI do cPanel.
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-check text-success me-3"></i>
                        <span class="text-gray-700">Primeiro consulta <code>DomainInfo/domains_data</code>.</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-check text-success me-3"></i>
                        <span class="text-gray-700">Se não encontrar detalhes, confere <code>DomainInfo/list_domains</code>.</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-check text-success me-3"></i>
                        <span class="text-gray-700">Nenhum recurso é criado, alterado ou removido.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
