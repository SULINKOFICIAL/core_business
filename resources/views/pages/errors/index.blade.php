@extends('layouts.app')

@section('title', 'Erros')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Erros do Sistema
</p>
<div class="card">
    <div class="card-body" id="errors-table">
        {{-- RESULTS HERE --}}
        {{-- RESULTS HERE --}}
        {{-- RESULTS HERE --}}
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
                loadTables();
            }
        });
    })

    $(document).on('click', '[data-bs-target="#modal-code"]', function () {
        var stackTrace = $(this).data('stacktrace');
        $('#stack-trace-text').val(stackTrace);
    });
    
</script>
@endsection