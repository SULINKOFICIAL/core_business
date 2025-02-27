@extends('layouts.app')

@section('title', $client->name)

@section('content')
@if ($responseApi)
    <P>ERRO NA API</P>
@endif
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-12">
                <div class="h-150px w-150px rounded bg-light d-flex align-items-center justify-content-center p-2">
                    @if ($client->logo)
                        <img src="{{ asset('storage/clientes/' . $client->id . '/logo.png') }}" alt="Logo do Cliente" class="img-fluid w-100 object-fit-contain">
                    @else
                        <div class="h-50px d-flex align-items-center justify-content-center">
                            <p class="m-0 fs-2x fw-bolder text-gray-300 text-uppercase">Sem logo</p>
                        </div>
                    @endif
                </div>
            </div>
            <div>
                <p class="fw-bold text-gray-700 fs-2x mb-2 text-uppercase lh-1">
                    {{ $client->name }}
                </p>
                <p class="text-gray-600 mb-0">
                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                </p>
                <div class="btn btn-sm btn-primary mt-2">
                    Ver contrato
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
@foreach ($sectors as $sector)
    <div class="col-6">
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="flaticon2-line-chart text-primary"></i>
                    </span>
                    <h3 class="card-label">
                        {{ $sector->name }}
                    </h3>
                </div>
            </div>
            <div class="card-body ">
                @foreach ($sector->groups as $group)
                <div class="rounded mb-4 p-4 bg-light">
                    <p class="text-capitalize mb-2 fw-bold text-gray-700">{{ $group->name }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($group->resources as $item)
                            <label class="form-check form-switch form-check-custom form-check-solid me-6">
                                <input class="form-check-input cursor-pointer input-features" type="checkbox" value="{{ $item->name }}" @if(isset($allowFeatures[$item->name]) && $allowFeatures[$item->name] == true) checked @endif/>
                                <span class="form-check-label fw-semibold text-gray-700 cursor-pointer">
                                {{ $item->name }}
                            </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
   </div>
@endforeach
</div>
<div class="d-flex justify-content-center">
    <a href="{{ route('clients.index') }}" class="btn btn-lg btn-primary mx-4">
        Voltar
    </a>
</div>
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        $(document).on('change', '.input-features', function(){

            // Obtém se esta checado ou não
            var checked = $(this).is(':checked');

            var name = $(this).val();

            // Busca OS
           $.ajax({
                type:'GET',
                url: "{{ route('systems.feature') }}",
                data: {
                    status: checked,
                    client_id: "{{ $client->id }}",
                     name: name,
                },
                success: function(response) {
                    toastr.success('Sucesso');
                },
            });
        });
    });
</script>
@endsection
