<div id="kt_app_header" class="app-header bg-dark" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
    <div class="app-container container-fluid d-flex align-items-center slk-dark shadow-lg" id="kt_app_header_container">
        <div class="w-100 container">
            <div class="row align-items-center">
                <div class="col-2">
                    <a href="{{ route('index') }}" class="d-flex align-items-center">
                        <img src="{{ asset('assets/media/images/logo_white_48px.png') }}" class="h-35px">
                    </a>
                </div>
                <div class="col-8 d-flex align-items-center justify-content-center">
                    @php($menuItems = header_menu_items())
                    <div class="app-header-menu d-none d-lg-flex align-items-center justify-content-center">
                        @include('layouts.partials.header-menu-desktop', ['menuItems' => $menuItems])
                    </div>
                    <div id="kt_app_header_menu_drawer" class="app-header-menu app-header-mobile-drawer d-lg-none" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle">
                        @include('layouts.partials.header-menu-mobile', ['menuItems' => $menuItems])
                    </div>
                </div>
                <div class="col-2">
                    <div class="app-navbar flex-shrink-0 justify-content-end">
                        @php($avatarUrl = Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/media/images/blank.png'))
                        <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                            <div class="cursor-pointer symbol symbol-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                <img src="{{ $avatarUrl }}" class="rounded-3" alt="user" />
                            </div>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <div class="menu-content d-flex align-items-center px-3">
                                        <div class="symbol symbol-50px me-5">
                                            <img alt="Logo" src="{{ $avatarUrl }}" />
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold d-flex align-items-center fs-5">{{ Auth::user()->name }}</div>
                                            <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ Str::limit(Auth::user()->email, 21) }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="separator my-2"></div>
                                <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                                    <a href="#" class="menu-link px-5">
                                        <span class="menu-title position-relative">
                                            Modo 
                                            <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                                <i class="ki-duotone ki-night-day theme-light-show fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></i>                        <i class="ki-duotone ki-moon theme-dark-show fs-2"><span class="path1"></span><span class="path2"></span></i>                    </span>
                                        </span>
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu" style="">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3 my-0">
                                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                                <span class="menu-icon" data-kt-element="icon">
                                                    <i class="ki-duotone ki-night-day fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></i>            </span>
                                                <span class="menu-title">
                                                    Light
                                                </span>
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3 my-0">
                                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                                <span class="menu-icon" data-kt-element="icon">
                                                    <i class="ki-duotone ki-moon fs-2"><span class="path1"></span><span class="path2"></span></i>            </span>
                                                <span class="menu-title">
                                                    Dark
                                                </span>
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3 my-0">
                                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                                <span class="menu-icon" data-kt-element="icon">
                                                    <i class="ki-duotone ki-screen fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>            </span>
                                                <span class="menu-title">
                                                    System
                                                </span>
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                        
                                </div>
                                <div class="menu-item px-5 my-1">
                                    <a href="{{ route('account.settings.edit') }}" class="menu-link px-5">Configurações da Conta</a>
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
