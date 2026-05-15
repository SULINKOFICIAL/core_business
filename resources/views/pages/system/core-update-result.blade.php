@extends('layouts.app')

@section('title', 'Atualização da Central')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold mb-0">Atualização da Central</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-danger d-flex align-items-start gap-4">
            <i class="fa-solid fa-triangle-exclamation fs-2 text-danger mt-1"></i>
            <div>
                <div class="fw-bold mb-1">Falha ao atualizar o core_business</div>
                <div class="text-gray-700">{{ $result['message'] ?? 'Erro desconhecido.' }}</div>
            </div>
        </div>

        <div class="d-flex flex-column gap-6">
            @foreach (($result['results'] ?? []) as $step)
                <div class="border rounded p-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="fw-bold fs-5">{{ $step['label'] ?? 'Etapa' }}</div>
                        @if (($step['success'] ?? false) === true)
                            <span class="badge badge-light-success">OK</span>
                        @else
                            <span class="badge badge-light-danger">Falhou</span>
                        @endif
                    </div>

                    <div class="text-gray-600 mb-3">
                        Código de saída: {{ $step['exit_code'] ?? '-' }}
                    </div>

                    <label class="form-label fw-semibold">Saída</label>
                    <pre class="bg-light text-gray-800 rounded p-4 mb-4" style="white-space: pre-wrap; word-break: break-word; max-height: 45vh; overflow: auto;">{{ $step['output'] ?? '' }}</pre>

                    <label class="form-label fw-semibold">Erro</label>
                    <pre class="bg-light-danger text-gray-800 rounded p-4 mb-0" style="white-space: pre-wrap; word-break: break-word; max-height: 45vh; overflow: auto;">{{ $step['error'] ?? '' }}</pre>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
