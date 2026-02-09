@extends('layouts.app')

@section('title', 'Detalhes do Pedido')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between min-h-60px px-6">
        <div>
            <h3 class="mb-1">Pedido #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h3>
            <span class="text-muted">Detalhes completos do pedido</span>
        </div>
        <div>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light">Voltar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4 mb-6">
            <div class="col-md-3">
                <div class="text-muted">Cliente</div>
                <div class="fw-bold text-gray-800">
                    {{ $order->client?->name ?? 'N/A' }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Status</div>
                <div>
                    @php($status = strtolower($order->status ?? ''))
                    @if (in_array($status, ['paid', 'pago']))
                        <span class="badge badge-light-success">Pago</span>
                    @elseif (in_array($status, ['canceled', 'cancelado']))
                        <span class="badge badge-light-danger">Cancelado</span>
                    @elseif (in_array($status, ['draft', 'rascunho']))
                        <span class="badge badge-light-secondary">Rascunho</span>
                    @elseif (in_array($status, ['pending_payment', 'pendente']))
                        <span class="badge badge-light-warning">Pendente</span>
                    @else
                        <span class="badge badge-light-info">{{ $order->status ?? '—' }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Total</div>
                <div class="fw-bold text-gray-800">R$ {{ number_format($order->total(), 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Pago em</div>
                <div class="text-gray-700">{{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}</div>
            </div>
        </div>

        <h5 class="mb-3">Itens</h5>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>Qtd</th>
                    <th>Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->item_type ?? $item->type ?? '—' }}</td>
                    <td>{{ $item->item_name_snapshot ?? $item->item_name ?? '—' }}</td>
                    <td>{{ $item->quantity ?? 1 }}</td>
                    <td>R$ {{ number_format($item->unit_price_snapshot ?? $item->item_value ?? 0, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->subtotal_amount ?? $item->item_value ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h5 class="mt-6 mb-3">Transações</h5>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th>ID</th>
                    <th>Status</th>
                    <th>Método</th>
                    <th>Gateway</th>
                    <th>Valor</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->transactions as $transaction)
                <tr>
                    <td>OT{{ $transaction->id }}</td>
                    <td>{{ $transaction->status }}</td>
                    <td>{{ $transaction->method ?? '—' }}</td>
                    <td>{{ $transaction->gateway?->name ?? '—' }}</td>
                    <td>R$ {{ number_format($transaction->amount ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Nenhuma transação registrada</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
