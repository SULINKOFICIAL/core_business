<button id="drawer_package" class="btn btn-danger btn-active-success position-fixed z-index-3" style="right: 200px; bottom: 50px">Trocar Pacote</button>
<form action="{{ route('packages.new', $client->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div
        class="bg-white"
        data-kt-drawer="true"
        data-kt-drawer-activate="true"
        data-kt-drawer-toggle="#drawer_package"
        data-kt-drawer-close="#kt_drawer_example_basic_close"
        data-kt-drawer-width="500px">
        <div class="card w-100 rounded-0">
            <div class="card-header pe-5">
                <div class="card-title">
                    <div class="d-flex justify-content-center flex-column me-3">
                        <span class="fs-4 fw-bold text-gray-700 me-1 lh-1">Trocar Pacote: <span class="text-danger">{{ $client->name }}</span></span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="btn btn-sm btn-icon btn-active-light-primary" id="kt_drawer_example_basic_close">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
            </div>
            <div class="card-body hover-scroll-overlay-y p-0">
                <div class="col-12 mb-4">
                    @foreach ($packages as $package)
                        @include('pages.clients._package')
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light py-6">
                <div class="d-flex justify-content-center align-items-center">
                    <button type="submit" class="btn btn-success btn-active-danger text-uppercase fw-bolder">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                        Trocar pacote do Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>