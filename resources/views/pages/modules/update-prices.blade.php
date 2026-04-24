@extends('layouts.app')

@section('title', 'Atualizar Preços - Módulos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Atualizar Preços dos Módulos
</p>

<form action="{{ route('modules.prices.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title fw-bold text-gray-700 m-0">Módulos disponíveis</h3>
            <div class="card-toolbar">
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Salvar alterações
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
            @if ($modules->isEmpty())
                <div class="alert alert-warning mb-0">
                    Nenhum módulo disponível para atualização de preço.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-7">
                                <th class="min-w-250px">Módulo</th>
                                <th class="min-w-180px">Tier/Faixa</th>
                                <th class="text-end min-w-150px">Preço atual</th>
                                <th class="text-end min-w-200px">Novo preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $module)
                                @php($isUsagePricing = ($module->pricing_type ?? 'Preço Fixo') === 'Preço Por Uso')
                                @if ($isUsagePricing && $module->pricingTiers->isNotEmpty())
                                    @foreach ($module->pricingTiers as $tier)
                                        <tr>
                                            <td class="fw-semibold text-gray-800">{{ $module->name }}</td>
                                            <td class="text-gray-700">
                                                <span class="badge badge-light-success">Até {{ $tier->usage_limit }} {{ $module->usage_label ?: 'usos' }}</span>
                                            </td>
                                            <td class="text-end text-gray-700">
                                                R$ {{ number_format((float) $tier->price, 2, ',', '.') }}
                                            </td>
                                            <td class="text-end">
                                                <input
                                                    type="text"
                                                    name="tier_prices[{{ $tier->id }}]"
                                                    value=""
                                                    class="form-control form-control-solid input-money text-end @error('tier_prices.' . $tier->id) is-invalid @enderror"
                                                    placeholder="R$ {{ number_format((float) $tier->price, 2, ',', '.') }}"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif ($isUsagePricing)
                                    <tr>
                                        <td class="fw-semibold text-gray-800">{{ $module->name }}</td>
                                        <td class="text-gray-700">
                                            <span class="badge badge-light-warning">Sem faixas</span>
                                        </td>
                                        <td class="text-end text-gray-700">
                                            <span class="badge badge-light-warning">Sem faixas cadastradas</span>
                                        </td>
                                        <td class="text-end">
                                            <input
                                                type="text"
                                                value=""
                                                class="form-control form-control-solid text-end"
                                                placeholder="Sem faixas para atualizar"
                                                disabled
                                            >
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="fw-semibold text-gray-800">{{ $module->name }}</td>
                                        <td class="text-gray-700">
                                            <span class="badge badge-light-primary">Preço fixo</span>
                                        </td>
                                        <td class="text-end text-gray-700">
                                            R$ {{ number_format((float) $module->value, 2, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <input
                                                type="text"
                                                name="prices[{{ $module->id }}]"
                                                value=""
                                                class="form-control form-control-solid input-money text-end @error('prices.' . $module->id) is-invalid @enderror"
                                                placeholder="R$ {{ number_format((float) $module->value, 2, ',', '.') }}"
                                            >
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-active-danger">
                        Salvar alterações
                    </button>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection
