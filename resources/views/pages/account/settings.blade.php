@extends('layouts.app')

@section('title', 'Configurações da Conta')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Configurações da Conta
</p>

<form action="{{ route('account.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-body">
            <div class="row g-6">
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-semibold">Foto de Perfil</label>
                    <div class="d-flex align-items-center gap-4">
                        <img
                            src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/media/images/blank.png') }}"
                            alt="Foto do usuário"
                            class="rounded-3"
                            style="width: 80px; height: 80px; object-fit: cover;"
                        >
                        <div class="w-100">
                            <input type="file" class="form-control form-control-solid" name="photo" accept="image/*">
                            @error('photo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <label class="form-label fw-semibold">Nome</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control form-control-solid"
                        value="{{ old('name', $user->name) }}"
                    >
                    @error('name')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control form-control-solid"
                        value="{{ old('email', $user->email) }}"
                    >
                    @error('email')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold">Nova Senha</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control form-control-solid"
                        placeholder="Deixe em branco para manter a senha atual"
                    >
                    @error('password')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold">Confirmar Nova Senha</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control form-control-solid"
                    >
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary btn-active-danger">
            Salvar Alterações
        </button>
    </div>
</form>
@endsection
