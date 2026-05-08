@extends('layouts.app')

@section('title', 'Sincronizar Planos')

@section('content')
<div class="row g-6">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">Sincronização em massa de planos</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-warning d-flex align-items-start p-5 mb-6">
                    <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mt-1"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-800 mb-1">Atenção</span>
                        <span class="text-gray-700">
                            Esta ação envia para todos os tenants a configuração consolidada do plano atual da central.
                        </span>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="text-gray-700 mb-2">
                        Tenants identificados para sincronização:
                    </div>
                    <div class="fs-2 fw-bolder text-primary">{{ $tenantsCount }}</div>
                </div>

                <form method="POST" action="{{ route('system.settings.subscriptions.sync.run') }}" id="form-subscriptions-sync-bulk">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-active-light-danger">
                        Sincronizar todos os tenants
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
<script>
    $(document).on('submit', '#form-subscriptions-sync-bulk', function (event) {
        const shouldProceed = window.confirm('Deseja mesmo sincronizar os planos para todos os tenants agora?');

        if (!shouldProceed) {
            event.preventDefault();
        }
    });
</script>
@endsection

