<!DOCTYPE html>
<html lang="pt-BR">
	<head>
        <base href="../../../" />
		<title>Login</title>
		@include('layouts.head')
	</head>
	<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center" style="background: url('{{ asset('assets/media/images/background.jpg') }}');background-position: left top;background-size: cover;">
        @include('layouts.config')
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<style>body { background-image: url('{{ asset("assets/media/auth/bg10.jpeg") }}'); } [data-bs-theme="dark"] body { background-image: url('{{ asset("assets/media/auth/bg10-dark.jpeg") }}'); }</style>
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<div class="d-flex flex-lg-row-fluid">
				</div>
				<div class="d-flex align-items-center pe-20 me-20">
					<div class="text-center">
						<img class="theme-light-show mx-auto mw-100 w-150px w-lg-400px mb-10 mb-lg-15" src="{{ asset('assets/media/logos/logo-central-dark.svg') }}" alt="" />
						<div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px px-7 py-15">
							<div class="d-flex flex-center flex-column align-items-stretch h-lg-100 px-10">
								<div class="d-flex flex-center flex-column flex-column-fluid">
									<form class="form w-100" method="POST" action="{{ route('login') }}">
										@csrf
										<h1 class="text-gray-800 fs-1 fw-bold text-center mb-4">Acessar Gerenciamento de miCore</h1>
										<div class="text-gray-600 fs-base text-center fw-semibold mb-7">
											Este sistema foi projetado para otimizar e centralizar as operações empresariais, facilitando a gestãoe promovendo maior eficiência.
										</div>
										<input type="text" placeholder="Email" name="email" class="form-control bg-transparent mb-2" required/>
										<input type="password" placeholder="Password" name="password" class="form-control bg-transparent mb-3" required />
										<div class="d-flex justify-content-end">
											<button type="submit" class="btn text-uppercase btn-success btn-active-danger fw-bolder w-100">
												Acessar
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>var hostUrl = "assets/";</script>
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
	</body>
</html>
