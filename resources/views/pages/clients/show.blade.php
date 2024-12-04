@extends('layouts.app')

@section('title', $client->name)

@section('content')
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
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <p class="fw-bold text-gray-700">Arquivos Gerais</p>
                <label class="form-check form-switch form-check-custom form-check-solid me-6">
                    <input class="form-check-input cursor-pointer input-features" type="checkbox" value="1" @if($actualFeatures->where('name', 'Arquivos Gerais')->first()['status']) checked @endif/>
                    <span class="form-check-label fw-semibold text-gray-700 cursor-pointer">
                        Ativo?
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="row">
@foreach ($modules as $module)
    <div class="col-6">
        <div class="card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="flaticon2-line-chart text-primary"></i>
                    </span>
                    <h3 class="card-label">
                        {{$module['nome']}}
                        <small>{{$module['frase']}}</small>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                @foreach ($module['recursos'] as $name => $resourse)
                <div class="rounded mb-4 p-4 bg-light">
                        <p class="text-capitalize mb-2 fw-bold text-gray-700">{{ $name }}</p>
                        <div class="d-flex">
                            @foreach ($resourse as $item)
                                <label class="form-check form-switch form-check-custom form-check-solid me-6">
                                    <input class="form-check-input cursor-pointer" type="checkbox" value="1" checked="checked"/>
                                    <span class="form-check-label fw-semibold text-gray-700 cursor-pointer">
                                        {{ $item }}
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
    <a href="#" class="btn btn-lg btn-danger mx-4">
        Desativar Site
    </a>
</div>
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        $(document).on('click', '.input-features', function(){

            // Obtém se esta checado ou não
            var checked = $(this).is(':checked');

           // Busca OS
           $.ajax({
                type:'GET',
                url: "{{ route('systems.feature') }}",
                data: {
                    status: checked,
                    client_id: "{{ $client->id }}",
                },
                success: function(response) {
                    toastr.success('Sucesso');
                },
            });
        });
    });
</script>
@endsection
