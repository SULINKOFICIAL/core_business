@extends('layouts.app')

@section('title', 'Configurações do Sistema')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Configurações do Sistema
</p>

<div class="row g-6">
    <div class="col-12 col-xl-8">
        <form action="{{ route('system.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Card principal para persistir os parametros SMTP do sistema. --}}
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">SMTP</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-6">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Mailer</label>
                            <input
                                type="text"
                                name="mailer"
                                class="form-control form-control-solid"
                                value="{{ old('mailer', $smtp['mailer']) }}"
                            >
                            @error('mailer')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Host</label>
                            <input
                                type="text"
                                name="host"
                                class="form-control form-control-solid"
                                value="{{ old('host', $smtp['host']) }}"
                            >
                            @error('host')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Porta</label>
                            <input
                                type="number"
                                name="port"
                                class="form-control form-control-solid"
                                value="{{ old('port', $smtp['port']) }}"
                            >
                            @error('port')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Criptografia</label>
                            <select name="encryption" class="form-select form-select-solid">
                                <option value="" {{ old('encryption', $smtp['encryption']) === null || old('encryption', $smtp['encryption']) === '' ? 'selected' : '' }}>Nenhuma</option>
                                <option value="ssl" {{ old('encryption', $smtp['encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="tls" {{ old('encryption', $smtp['encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                            </select>
                            @error('encryption')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Remetente</label>
                            <input
                                type="text"
                                name="from_name"
                                class="form-control form-control-solid"
                                value="{{ old('from_name', $smtp['from_name']) }}"
                            >
                            @error('from_name')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Usuário SMTP</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control form-control-solid"
                                value="{{ old('username', $smtp['username']) }}"
                            >
                            @error('username')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Senha SMTP</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control form-control-solid"
                                placeholder="{{ $smtp['hasPassword'] ? 'Preencha apenas para alterar a senha' : 'Digite a senha SMTP' }}"
                            >
                            @error('password')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">E-mail remetente</label>
                            <input
                                type="email"
                                name="from_address"
                                class="form-control form-control-solid"
                                value="{{ old('from_address', $smtp['from_address']) }}"
                            >
                            @error('from_address')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <div class="col-12 col-xl-4">
        <form action="{{ route('system.settings.test') }}" method="POST">
            @csrf

            {{-- Card separado para validar a configuracao sem alterar a tela principal. --}}
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Teste de Envio</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-6">
                        <label class="form-label fw-semibold">Nome do destinatário</label>
                        <input
                            type="text"
                            name="test_name"
                            class="form-control form-control-solid"
                            value="{{ old('test_name') }}"
                            placeholder="Opcional"
                        >
                        @error('test_name')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="form-label fw-semibold">E-mail de teste</label>
                        <input
                            type="email"
                            name="test_email"
                            class="form-control form-control-solid"
                            value="{{ old('test_email') }}"
                            placeholder="email@dominio.com"
                        >
                        @error('test_email')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="text-gray-600 fs-7 mb-6">
                        O sistema usará a configuração SMTP salva acima para disparar um e-mail simples de validação.
                    </div>

                    <a
                        href="{{ route('system.settings.preview') }}"
                        target="_blank"
                        class="btn btn-light-primary btn-active-danger w-100 mb-3"
                    >
                        Preview do HTML
                    </a>

                    <button type="submit" class="btn btn-success btn-active-danger w-100">
                        Enviar E-mail de Teste
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
