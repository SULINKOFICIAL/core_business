<div class="modal-header py-3 bg-dark">
    <h5 class="modal-title text-white">Detalhes do pedido 
        <span class="badge badge-light-primary">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
    </h5>
    <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
        <span class="svg-icon svg-icon-2x fw-bolder">X</span>
    </div>
</div>
<div class="modal-body p-0">
    <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables mb-0">
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
                    {{ $item->item_name }}
                </td>
                <td class="text-start py-1">
                    <span class="text-gray-700 lh-1">R$ {{ number_format($item->item_value, 2, ',', '.') }}</span>
                </td>
                <td class="p-0"></td>
                <td class="p-0"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>