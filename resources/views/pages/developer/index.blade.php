@extends('layouts.app')

@section('content')
<div id="kt_app_toolbar" class="app-toolbar ">

            <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container  container-xxl d-flex align-items-start ">
            <!--begin::Toolbar container-->
<div class="d-flex flex-column flex-row-fluid">     
   <!--begin::Toolbar wrapper-->
    <div class="d-flex align-items-center pt-1">     
        
    
    </div>
    <!--end::Toolbar wrapper--->

    
<!--begin::Page title-->
<div class="page-title d-flex align-items-center me-3 mb-4 pt-9 pt-lg-17 mb-lg-15">
    <div class="btn btn-icon btn-custom h-65px w-65px me-6">
        <img alt="Logo" src="/metronic8/demo32/assets/media/svg/misc/layer.svg" class="h-40px">
    </div>

    <!--begin::Title-->
    <h1 class="page-heading d-flex text-white fw-bolder fs-2 flex-column justify-content-center my-0">
        Metronic - Multi-platform  Framewok
                    <!--begin::Description-->
            <span class="page-desc fs-6 fw-bold pt-4">
                <i class="ki-outline ki-star fs-6 text-warning me-2"></i> <span class="custom-text me-3 lh-0">4.89</span> <span class="d-flex align-items-center lh-0 fs-7">7,834 Reviews <span class="bullet h-6px w-6px mx-3"></span> #1 Admin Dashboard Template</span>            </span>
            <!--end::Description-->
            </h1>
    <!--end::Title-->    
</div>
<!--end::Page title--> 
    <!--begin::Toolbar wrapper--->
    <div class="d-flex justify-content-between flex-wrap gap-4 gap-lg-10">   
        
<!--begin::Toolbar menu-->
<div class="app-toolbar-menu menu menu-rounded menu-gray-800 menu-state-bg flex-wrap fs-5 fw-semibold ">
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link active" href="/metronic8/demo32/?page=index">
                <span class="menu-title">Summary</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/projects/list">
                <span class="menu-title">Pages</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/user-management/users/list">
                <span class="menu-title">Members</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/subscriptions/list">
                <span class="menu-title">Apps</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/file-manager/folders">
                <span class="menu-title">Help</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/support-center/contact">
                <span class="menu-title">Support</span>
			</a>
		</div>
		<!--end::Menu item-->
			<!--begin::Menu item-->
		<div class="menu-item pb-xl-8 pb-4 mt-5 mt-lg-0">
			<a class="menu-link " href="/metronic8/demo32/?page=apps/customers/list">
                <span class="menu-title">Customers</span>
			</a>
		</div>
		<!--end::Menu item-->
	</div>
<!--begin::Toolbar menu-->        
        
<!--begin::Wrapper-->
<div class="d-flex flex-align-items flex-wrap gap-3 gap-xl-0 mb-7 mt-n1">
    <!--begin::Users group-->
    <div class="symbol-group symbol-hover flex-nowrap me-5">
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" data-bs-original-title="Alan Warden" data-kt-initialized="1">
                                    <span class="symbol-label bg-warning text-inverse-warning fw-bold">A</span>
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Michael Eberon" data-bs-original-title="Michael Eberon" data-kt-initialized="1">
                                    <img alt="Pic" src="/metronic8/demo32/assets/media/avatars/300-11.jpg">
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Jacob Jones" data-bs-original-title="Jacob Jones" data-kt-initialized="1">
                                    <img alt="Pic" src="/metronic8/demo32/assets/media/avatars/300-9.jpg">
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" data-bs-original-title="Susan Redwood" data-kt-initialized="1">
                                    <span class="symbol-label bg-primary text-inverse-primary fw-bold">S</span>
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Jane Cooper" data-bs-original-title="Jane Cooper" data-kt-initialized="1">
                                    <img alt="Pic" src="/metronic8/demo32/assets/media/avatars/300-2.jpg">
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" data-bs-original-title="Perry Matthew" data-kt-initialized="1">
                                    <span class="symbol-label bg-danger text-inverse-danger fw-bold">P</span>
                            </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Cody Fishers" data-bs-original-title="Cody Fishers" data-kt-initialized="1">
                                    <img alt="Pic" src="/metronic8/demo32/assets/media/avatars/300-7.jpg">
                            </div>
                 
    </div>
    <!--end::Users group-->

    <!--begin::Actions-->
    <div class="d-flex align-items-center flex-shrink-0">
        <a href="#" class="btn btn-sm btn-flex btn-primary px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_invite_friends">
            <i class="ki-outline ki-plus-square fs-2"></i>            Invite 
        </a>   

        <a href="#" class="btn btn-sm btn-flex btn-light ms-5" data-bs-toggle="modal" data-bs-target="#kt_modal_new_target">
            Set Your Target
        </a> 
    </div>
    <!--end::Actions-->
</div>
<!--end::Wrapper-->    
    </div>
    <!--end::Toolbar wrapper--->     
</div>
<!--end::Toolbar container--->        </div>
        <!--end::Toolbar container-->
    </div>
@endsection