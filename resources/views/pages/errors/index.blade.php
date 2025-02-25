@extends('layouts.app')

@section('title', 'Erros')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Erros do Sistema
</p>
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start" width="3%">Cliente</th>
                    <th class="text-start">Mensagem</th>
                    <th class="text-start px-0" width="30%">Código</th>
                    <th class="text-center px-0">Código de Status</th>
                    <th class="text-center px-0">Endereço Ip</th>
                    <th class="text-center px-0">Criado Em</th>
                </tr>
            </thead>
            <tbody id="errors-table">
                {{-- RESULTS HERE --}}
                {{-- RESULTS HERE --}}
                {{-- RESULTS HERE --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
@parent
    @include('pages.errors.modals')
@endsection

@section('custom-footer')
@parent
<script>

    $(document).ready(function (){
        url = "{{ route('errors.show') }}"

        $.ajax({
            type: 'GET',
            url: url,
            success: function(response){
                $('#errors-table').html(response);
            }
        });
    })

    $(document).on('click', '[data-bs-target="#modal-code"]', function () {
        var stackTrace = $(this).data('stacktrace');
        $('#stack-trace-text').val(stackTrace);
    });
    
</script>
@endsection