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
		<script>var hostUrl = "assets/";</script>
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
	</body>
</html>
