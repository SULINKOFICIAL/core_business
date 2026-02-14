@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start">Pedido</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Itens</th>
                    <th class="text-start">Total</th>
                    <th class="text-start">Criado</th>
                    <th class="text-start">Pago</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td class="text-start">
                        <span class="fw-bolder text-gray-700">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="text-start">
                        @if ($order->client)
                            <a href="{{ route('clients.show', $order->client->id) }}" class="text-gray-700 text-hover-primary fw-bold">
                                {{ $order->client->name }}
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">{{ $order->type ?? '—' }}</span>
                    </td>
                    <td class="text-start">
                        @php
                            $status = strtolower($order->status ?? '');
                        @endphp
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
                    </td>
                    <td class="text-start">
                        <span class="text-gray-700">{{ $order->items->count() }}</span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-700 fw-bold">R$ {{ number_format($order->total(), 2, ',', '.') }}</span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">{{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-primary btn-active-success">
                            Detalhes
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-end">
    {{ $orders->links() }}
</div>
@endsection
