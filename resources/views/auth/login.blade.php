<!DOCTYPE html>
<html lang="pt-BR">
	<head>
        <base href="../../../" />
		<title>Login</title>
		@include('layouts.head')
	</head>
	<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center" style="background: url('{{ asset('assets/media/images/background_2026.png') }}');background-position: left top;background-size: cover;">
        @include('layouts.config')
		<div class="min-vh-100 d-flex align-items-center justify-content-center py-4 px-3">
			<div class="text-center w-100 w-md-600px">
				<div class="card shadow-sm rounded-4">
					<div class="card-body">
						<img class="img-fluid mb-4 h-50px h-md-70px" src="{{ asset('assets/media/images/logo_black.png') }}" alt="" />
						<form class="w-100" method="POST" action="{{ route('login') }}">
							@csrf
							<h1 class="text-gray-800 fs-3 fw-bold text-center mb-3">Acessar Gerenciamento de miCore</h1>
							<p class="text-gray-600 text-center fw-semibold mb-4">
								Este sistema foi projetado para otimizar e centralizar as operações empresariais, facilitando a gestão e promovendo maior eficiência.
							</p>
							<input type="text" placeholder="Email" name="email" class="form-control bg-transparent mb-2" required />
							<input type="password" placeholder="Password" name="password" class="form-control bg-transparent mb-3" required />
							<div class="form-check d-flex align-items-center mb-3">
								<input class="form-check-input me-2" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
								<label class="form-check-label text-gray-700 fw-semibold" for="remember">
									Lembrar de mim
								</label>
							</div>
							<button type="submit" class="btn text-uppercase btn-success fw-bolder w-100">
								Acessar
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		<script>var hostUrl = "assets/";</script>
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
	</body>
</html>
