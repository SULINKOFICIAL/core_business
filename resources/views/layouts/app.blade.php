<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>@yield('title') - Core Business</title>
	    @include('layouts.head')
    </head>
	<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" class="app-default">
        @include('layouts.config')
		<div class="d-flex flex-column flex-root app-root" >
			<div class="app-page flex-column flex-column-fluid">
				@include('layouts.header')
				<div class="app-wrapper flex-column flex-row-fluid">
					<div class="app-main flex-column flex-row-fluid py-10">
                        <div class="container container-fluid">
						    @yield('content')
                        </div>
                    </div>
				</div>
			</div>
		</div>
        @yield('modals')
		<script>var hostUrl = "assets/";</script>
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/datatable-input.js') }}"></script>
        <script src="{{ asset('assets/js/custom.bundle.js') }}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/pt.min.js"></script>
        <script>

            // Configura Toaster
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toastr-bottom-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Verifica se existe um alerta a exibir
			var message = '{!! session("message") !!}';
            var type = "{!! session('type') !!}";

            // Exibe o alerta
            if(message){
                switch (type) {
                    case 'success':
                        toastr.success(message);
                        break;
                    case 'error':
                        toastr.success(message);
                        break;
                    case 'warning':
                        toastr.success(message);
                        break;
                    default:
                        toastr.info(message);
                        break;
                }
            }
        </script>
        @yield('custom-footer')
	</body>
</html>
