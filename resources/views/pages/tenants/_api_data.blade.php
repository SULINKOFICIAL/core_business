@if ($apiError)
    <div class="alert alert-danger d-flex align-items-center p-5 mb-4">
        <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">Falha ao consultar a instalação</h4>
            <span>{{ $errorMessage ?? 'Não foi possível obter os dados via API neste momento.' }}</span>
        </div>
    </div>
@else
    <div class="row g-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="fw-bolder text-gray-700 fs-4 text-uppercase mb-3">Assinatura</p>
                    <div class="d-flex flex-column align-items-start mb-3">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Início</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">
                            {{ isset($allowSubscription['start_date']) ? date('d/m/Y', strtotime($allowSubscription['start_date'])) : 'Sem data' }}
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Fim</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">
                            {{ isset($allowSubscription['end_date']) ? date('d/m/Y', strtotime($allowSubscription['end_date'])) : 'Sem data' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="fw-bolder text-gray-700 fs-4 text-uppercase mb-3">Usuários</p>
                    <div class="d-flex flex-column align-items-start mb-3">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Total</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">{{ $totalUsers }}</p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Limite</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">{{ $limitUsers }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="fw-bolder text-gray-700 fs-4 text-uppercase mb-3">Armazenamento</p>
                    <div class="d-flex flex-column align-items-start mb-3">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Usado</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">{{ $totalStorageGB }} GB</p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-6">Limite</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">{{ $limitStorageGB }} GB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
