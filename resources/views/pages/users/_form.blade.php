<div class="row">
    <div class="mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $user->name ?? old('name') }}" required>
        @error('name')
            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Email</label>
        <input type="email" class="form-control form-control-solid" placeholder="Email" name="email" value="{{ $user->email ?? old('email') }}" required>
        @error('email')
            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 col-md-6">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 @if (!isset($user)) required @endif">Senha</label>
        <input type="password" class="form-control form-control-solid" placeholder="Senha" name="password" @if (!isset($user)) required @endif>
        @if (isset($user))
            <div class="text-muted fs-8 mt-1">Preencha apenas se desejar alterar a senha.</div>
        @endif
        @error('password')
            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 col-md-6">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 @if (!isset($user)) required @endif">Confirmar Senha</label>
        <input type="password" class="form-control form-control-solid" placeholder="Confirmar senha" name="password_confirmation" @if (!isset($user)) required @endif>
    </div>
</div>
