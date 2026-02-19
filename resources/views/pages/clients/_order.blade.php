<div class="modal-header py-3 bg-dark">
    <h5 class="modal-title text-white">Detalhes do pedido 
        <span class="badge badge-light-primary">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
    </h5>
    <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
        <span class="svg-icon svg-icon-2x fw-bolder">X</span>
    </div>
</div>
<div class="modal-body p-2">
    <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables mb-6">
        <thead class="bg-dark">
            <tr class="fw-bold fs-6 text-white tr-rounded">
                <th>Itens do Pedido</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="text-start">
            @foreach ($order->items as $item)
            <tr class="text-muted bg-light">
                <td class="text-gray-700 fw-semibold">
                    @if ($item->action == 'Upgrade')
                    <span class="badge badge-light-success">{{ $item->action }}</span>
                    @elseif($item->action == 'Downgrade')
                    <span class="badge badge-light-danger">{{ $item->action }}</span>
                    @elseif($item->action == 'Adição')
                    <span class="badge badge-light-primary">{{ $item->action }}</span>
                    @elseif($item->action == 'Alteração')
                    <span class="badge badge-light-warning">{{ $item->action }}</span>
                    @else
                    <span class="badge badge-light-info">{{ $item->action }}</span>
                    @endif
                    {{ $item->item_name_snapshot ?? $item->item_name }}
                </td>
                <td class="text-start py-1">
                    @php($itemValue = $item->item_value ?? $item->subtotal_amount ?? 0)
                    <span class="text-gray-700 lh-1">R$ {{ number_format($itemValue, 2, ',', '.') }}</span>
                </td>
                <td class="p-0"></td>
                <td class="p-0"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables mb-0">
        <thead class="bg-dark">
            <tr class="fw-bold fs-6 text-white tr-rounded">
                <th>ID PagarMe</th>
                <th>Status</th>
                <th>Valor</th>
                <th>Método</th>
                <th>Horário do pagamento</th>
            </tr>
        </thead>
        <tbody class="text-start">
            @foreach ($order->transactions as $transaction)
                <tr>
                    <td class="text-gray-700 fw-bolder">
                        {{ $transaction->subscription->pagarme_subscription_id }}
                    </td>
                    <td class="text-gray-700 fw-semibold">
                        @if ($transaction->status == 'paid')
                        <span class="badge badge-light-success">Pago</span>
                        @elseif($transaction->status == 'canceled')
                        <span class="badge badge-light-danger">Cancelado</span>
                        @elseif($transaction->status == 'processing')
                        <span class="badge badge-light-primary">Processando</span>
                        @elseif($transaction->status == 'pending')
                        <span class="badge badge-light-warning">Pendente</span>
                        @elseif($transaction->status == 'failed')
                        <span class="badge badge-light-danger">Falhou</span>
                        @elseif($transaction->status == 'refunded')
                        <span class="badge badge-light-danger">Estornado</span>
                        @endif
                    </td>
                    <td class="text-gray-700 fw-semibold">
                        @if($transaction->currency == 'BRL')
                        R$
                        @endif
                        {{ $transaction->amount }}
                    </td>
                    <td>
                        @if ($transaction->method == 'credit_card')
                            <span class="fw-bolder text-success">
                                Cartão de Crédito
                            </span>
                        @else
                            <span class="badge badge-light text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-gray-700 fw-bolder">
                            {{ $transaction->paid_at 
                                ? $transaction->paid_at->format('d/m/Y') . ' às ' . $transaction->paid_at->format('H:i:s') 
                                : '-' 
                            }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
