@extends('layouts.app')

@section('title', 'Cupons')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Cupons</h3>
                <span class="text-muted">Gerencie os cupons de desconto da central</span>
            </div>
            <div class="d-flex gap-2">
                <div class="text-end text-muted">
                    Total: {{ $coupons->total() }}
                </div>
                <a href="{{ route('coupons.create') }}" class="btn btn-primary btn-active-success">
                    Novo cupom
                </a>
            </div>
        </div>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start">Código</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Valor</th>
                    <th class="text-start">Validade</th>
                    <th class="text-start">Usos</th>
                    <th class="text-start">Status</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($coupons as $coupon)
                <tr>
                    <td class="text-start">
                        <span class="fw-bolder text-gray-700">{{ $coupon->code }}</span>
                    </td>
                    <td class="text-start">
                        @if ($coupon->type === 'percent')
                            <span class="badge badge-light-primary">Percentual</span>
                        @elseif ($coupon->type === 'fixed')
                            <span class="badge badge-light-success">Valor fixo</span>
                        @else
                            <span class="badge badge-light-warning">Trial</span>
                        @endif
                    </td>
                    <td class="text-start">
                        @if ($coupon->type === 'trial')
                            <span class="text-gray-700">{{ $coupon->trial_months ?? 1 }} mês(es)</span>
                        @elseif ($coupon->type === 'percent')
                            <span class="text-gray-700">{{ number_format($coupon->amount ?? 0, 2, ',', '.') }}%</span>
                        @else
                            <span class="text-gray-700">R$ {{ number_format($coupon->amount ?? 0, 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">
                            {{ $coupon->starts_at?->format('d/m/Y') ?? '—' }} →
                            {{ $coupon->ends_at?->format('d/m/Y') ?? '—' }}
                        </span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-700">
                            {{ $coupon->redeemed_count ?? 0 }}
                            @if ($coupon->max_redemptions)
                                / {{ $coupon->max_redemptions }}
                            @endif
                        </span>
                    </td>
                    <td class="text-start">
                        @if ($coupon->is_active)
                            <span class="badge badge-light-success">Ativo</span>
                        @else
                            <span class="badge badge-light-danger">Inativo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-sm btn-primary btn-active-success me-2">
                            Editar
                        </a>
                        <a href="{{ route('coupons.destroy', $coupon->id) }}" class="btn btn-sm btn-light">
                            {{ $coupon->is_active ? 'Desabilitar' : 'Ativar' }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-end">
    {{ $coupons->links() }}
</div>
@endsection
