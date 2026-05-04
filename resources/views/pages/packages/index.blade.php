@extends('layouts.app')

@section('title', 'Pacotes')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Pacotes
</p>
<div class="row">
    @foreach ($packages as $package)
    <div class="col-12 col-md-6 col-lg-4 d-flex">
        <div class="card w-100 mb-6">
            <div class="card-header d-flex align-items-center justify-content-between min-h-60px px-6">
                <div class="w-75">
                    @php
                        $packagePrice = (float) $package->modules->sum(fn ($module) => (float) ($module->pivot->price ?? $module->value ?? 0));
                    @endphp
                    <a href="{{ route('modules.edit', $package->id) }}" class="mb-0 fw-bolder @if ($package->status == 0) text-danger @else text-gray-700 @endif text-hover-primary m-0 fs-5 text-uppercase lh-1">
                        {{ Str::limit($package->name, 25) }}
                        @if ($package->popular)
                            <span class="badge badge-success ms-1">Popular</span>
                        @endif
                    </a>
                    <p class="text-gray-500 mb-0 fw-semibold fs-7 lh-1">
                        <span class="fw-bolder text-primary">{{ $package->duration_days }}</span> dias - <span class="text-success value-module">R$ {{ number_format($packagePrice, 2, ',', '.') }}</span> -
                        <span class="text-danger fw-bolder"> {{ number_format($package->size_storage / 1073741824, 2) }}</span> GB
                    </p>
                </div>
                <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fs-8 fw-bolder text-gray-600 mb-1">Posição</label>
                    <input
                        type="number"
                        min="1"
                        class="form-control form-control-sm form-control-solid js-package-order-input"
                        data-url="{{ route('packages.order.update', $package->id) }}"
                        value="{{ (int) ($package->order ?? 1) }}"
                    >
                </div>
                <p class="fw-bolder text-gray-700 text-uppercase mb-1">Módulos</p>
                @if ($package->modules->count())
                    @foreach ($package->modules as $key => $group)
                    <p class="text-gray-700 m-0 fs-7"><span class="fw-bolder">{{ $key + 1 }}.</span> {{ $group->name }}</p>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center fs-7 fw-bold mb-0">
                        Sem Módulos
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="d-flex mt-4">
    <a href="{{ route('packages.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Criar Pacote
    </a>
</div>
@endsection

@section('custom-footer')
    @parent
    <script>
        $(function () {
            let packageOrderRequest = false;

            $(document).on('focus', '.js-package-order-input', function () {
                $(this).data('last-value', $(this).val());
            });

            $(document).on('change', '.js-package-order-input', function () {
                if (packageOrderRequest) {
                    return;
                }

                const input = $(this);
                const url = input.data('url');
                const order = Number(input.val() || 0);
                const previousValue = input.data('last-value') || 1;

                if (!url) {
                    return;
                }

                if (!Number.isInteger(order) || order < 1) {
                    input.val(previousValue);
                    toastr.warning('Informe uma posição válida (mínimo 1).');
                    return;
                }

                packageOrderRequest = true;
                input.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order: order
                    },
                    success: function () {
                        input.data('last-value', order);
                        toastr.success('Posição atualizada com sucesso.');
                    },
                    error: function () {
                        input.val(previousValue);
                        toastr.error('Não foi possível atualizar a posição.');
                    },
                    complete: function () {
                        packageOrderRequest = false;
                        input.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
