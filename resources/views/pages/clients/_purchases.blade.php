
<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="w-150px text-start">ID</th>
                    <th class="text-start">Descrição das alterações</th>
                    <th class="text-start">Valor</th>
                    <th class="text-center">Método</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-start">
                @foreach ($client->purchases()->orderBy('created_at', 'DESC')->get() as $purchase)
                <tr>
                    <td class="w-100px text-start px-2">
                        <p class="text-gray-700 fw-bolder mb-0">
                            #{{ str_pad($purchase->id, 4, '0', STR_PAD_LEFT) }}
                        </p>
                        <span class="text-gray-600 fs-8">
                            {{ $purchase->purchase_date->format('d/m/Y') }} às {{ $purchase->purchase_date->format('H:i:s') }}
                        </span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">
                        @if ($purchase->type == 'Pacote Atribuido')
                            Pacote atribuido <span class="fw-bolder text-success">{{ $purchase->package->name }}</span>
                        @endif
                        @if ($purchase->type == 'Pacote Trocado')
                            Pacote trocado de <span class="fw-bolder text-danger">{{ $purchase->previousPackage->name }}</span> para <span class="fw-bolder text-success">{{ $purchase->package->name }}</span>.
                        @endif
                        </span>
                        @if ($purchase->description)
                        <p class="text-gray-700 fw-bold fs-8 mb-0">Observação: {{ $purchase->description }}</p>
                        @endif
                    </td>
                    <td class="text-start">
                        <span class="text-gray-700 fw-bold">R$ {{ number_format($purchase->total(), 2, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="text-gray-700 fw-bold">{{ $purchase->method }}</span>
                    </td>
                    <td class="text-center">
                        @if ($purchase->status == 'Pago')
                        <span class="badge badge-light-success">Pago</span>
                        @elseif ($purchase->status == 'Cancelado')
                        <span class="badge badge-light-danger">Cancelado</span>
                        @else
                        <span class="badge badge-light-warning">Pendente</span>
                        @endif
                    </td>
                </tr>
                @foreach ($purchase->items as $item)
                <tr class="text-muted bg-light">
                    <td class="p-0"></td>
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>