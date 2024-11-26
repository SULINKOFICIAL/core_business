<!DOCTYPE html>
<html lang="pt-BR">
	<head>
        <base href="../../../" />
		<title>Login</title>
		@include('layouts.head')
	</head>
	<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
        @include('layouts.config')
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<style>body { background-image: url('{{ asset("assets/media/auth/bg10.jpeg") }}'); } [data-bs-theme="dark"] body { background-image: url('{{ asset("assets/media/auth/bg10-dark.jpeg") }}'); }</style>
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<div class="d-flex flex-lg-row-fluid">
					<div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                        <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" src="{{ asset('assets/media/logos/sulink-core-dark.svg') }}" alt="" />
                        <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">Sistema de Gerenciamento Core</h1>
                        <div class="text-gray-600 fs-base text-center fw-semibold">
                            Este sistema foi projetado para otimizar e centralizar as operações empresariais,
                            <a href="#" class="opacity-75-hover text-primary me-1">facilitando a<br> gestão</a> e promovendo maior eficiência.
                            Com funcionalidades integradas e automação de processos,<br /> o Core é a solução ideal para
                            <a href="#" class="opacity-75-hover text-primary me-1">empresas que buscam evolução</a> na gestão de seus recursos.
                        </div>
                    </div>

				</div>
				<div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
					<div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
						<div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
							<div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
								<form class="form w-100" method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <h1 class="text-gray-900 fw-bolder text-center mb-3">Acessar</h1>
                                    <input type="text" placeholder="Email" name="email" class="form-control bg-transparent mb-8" required/>
                                    <input type="password" placeholder="Password" name="password" class="form-control bg-transparent mb-3" required />
                                    <button type="submit" class="btn btn-primary">
                                        Acessar
                                    </button>
								</form>
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
