<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
    <div class="app-container container-fluid d-flex align-items-center slk-dark shadow-lg" id="kt_app_header_container">
        <div class="w-100">
            <div class="row align-items-center">
                <div class="col-2">
                    <a href="{{ route('index') }}" class="d-flex align-items-center">
                        <img src="{{ asset('assets/media/logos/logo-central-dark.svg') }}" class="w-200px">
                    </a>
                </div>
                <div class="col-8 d-flex align-items-center justify-content-center">
                    <div class="app-header-menu app-header-mobile-drawer align-items-center justify-content-center" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                        <a href="{{ route('clients.index') }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ Request::routeIs('clients.index') ? 'text-warning' : 'text-white' }}">
                            Clientes
                        </a>
                        <a href="{{ route('packages.index') }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ Request::routeIs('packages.index') ? 'text-warning' : 'text-white' }}">
                            Pacotes
                        </a>
                        <a href="{{ route('modules.index') }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ Request::routeIs('modules.index') ? 'text-warning' : 'text-white' }}">
                            Módulos
                        </a>
                        <a href="{{ route('groups.index') }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ Request::routeIs('groups.index') ? 'text-warning' : 'text-white' }}">
                            Grupo de Recursos
                        </a>
                        <a href="{{ route('resources.index') }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ Request::routeIs('resources.index') ? 'text-warning' : 'text-white' }}">
                            Recursos
                        </a>
                        <div class="menu menu-column menu-gray-600 menu-active-primary menu-hover-light-primary menu-here-light-primary menu-show-light-primary fw-semibold w-175px" data-kt-menu="true">
                            <div class="menu-item" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start">
                                <a href="#" class="menu-link">
                                    <span class="menu-title text-uppercase text-white fs-7">
                                        Configuração
                                        <i class="fa-solid fa-angle-down text-white fs-8 ms-2"></i>
                                    </span>
                                </a>
                                <div class="menu-sub menu-sub-dropdown w-175px py-2">
                                    <div class="menu-item">
                                        <a href="{{ route('subscriptions.generate') }}" class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-chart-simple-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                            </span>
                                            <span class="menu-title">Gerar Renovações</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="{{ route('subscriptions.charge') }}" class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-chart-simple-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                            </span>
                                            <span class="menu-title">Cobrar</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="{{ route('subscriptions.expired') }}" class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-chart-simple-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                            </span>
                                            <span class="menu-title">Expirar renovações</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="{{ route('tickets.index') }}" class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-notification-on fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </span>
                                            <span class="menu-title">Tickets</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="{{ route('errors.index') }}" class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-calendar-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </span>
                                            <span class="menu-title">Errors</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="app-navbar flex-shrink-0 justify-content-end">
                        <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                            <div class="cursor-pointer symbol symbol-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                <img src="{{ asset('assets/media/images/blank.png') }}" class="rounded-3" alt="user" />
                            </div>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <div class="menu-content d-flex align-items-center px-3">
                                        <div class="symbol symbol-50px me-5">
                                            <img alt="Logo" src="{{ asset('assets/media/images/blank.png') }}" />
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold d-flex align-items-center fs-5">{{ Auth::user()->name }}</div>
                                            <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ Str::limit(Auth::user()->email, 21) }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="separator my-2"></div>
                                <div class="menu-item px-5 my-1">
                                    <a href="#" class="menu-link px-5">Configurações da Conta</a>
                                </div>
                                <div class="menu-item px-5">
                                    <a href="{{ route('logout') }}" class="menu-link px-5">Sair</a>
                                </div>
                            </div>
                        </div>
                        <div class="app-navbar-item d-lg-none ms-2 me-n2" title="Show header menu">
                            <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px" id="kt_app_header_menu_toggle">
                                <i class="ki-duotone ki-element-4 fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
