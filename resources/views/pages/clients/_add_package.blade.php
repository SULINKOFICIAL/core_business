<button id="drawer_package" class="btn btn-success btn-active-success position-fixed" style="right: 200px; bottom: 50px">Atribuir Pacote</button>
<form action="{{ route('packages.assign', $client->id) }}" method="POST" enctype="multipart/form-data">
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
                        <span class="fs-4 fw-bold text-gray-700 me-1 lh-1">Atribuir Pacote: <span class="text-danger">{{ $client->name }}</span></span>
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
                    <label class="border-bottom border-bottom-dashed border-600 w-100 cursor-pointer p-5 bg-hover-light" for="package-{{ $package->id }}">
                        <div class="d-flex justify-content-between">
                            <div class="text-gray-500">
                                <p class="fw-bolder mb-0 text-uppercase text-gray-700 lh-1">
                                    {{ $package->name }}
                                </p>
                                <span class="fw-bolder text-primary">{{ $package->duration_days }}</span> dias - <span class="text-success value-module">R$ {{ number_format($package->value, 2, ',', '.') }}</span>
                            </div>
                            <div class="form-check form-check-custom form-check-success form-check-solid">
                                <input class="form-check-input" name="package_id" value="{{ $package->id }}" type="radio" @if($package->id == $client->package_id) checked @endif id="package-{{ $package->id }}" required/>
                            </div>
                        </div>
                        @if ($package->modules->count())
                        <div class="d-flex flex-wrap mt-2 gap-2">
                            @foreach ($package->modules as $key => $group)
                            <span class="badge badge-light-primary">{{ $group->name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light py-6">
                <div class="d-flex justify-content-center align-items-center">
                    <button type="submit" class="btn btn-success btn-active-danger text-uppercase fw-bolder">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                        Atribuir ao Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>