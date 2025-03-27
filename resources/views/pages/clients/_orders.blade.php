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
                @foreach ($client->orders()->orderBy('created_at', 'DESC')->get() as $order)
                <tr class="show-div">
                    <td class="w-100px text-start px-2">
                        <p class="text-gray-700 text-hover-primary fw-bolder mb-0 cursor-pointer show-details" data-order="{{ $order->id }}">
                            #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            <i class="fa-solid fa-eye show-after"></i>
                        </p>
                        <span class="text-gray-600 fs-8">
                            {{ $order->created_at->format('d/m/Y') }} às {{ $order->created_at->format('H:i:s') }}
                        </span>
                    </td>
                    <td class="text-start">
                        <span class="text-gray-600">
                            @if ($order->type == 'Pacote Atribuido')
                                Pacote atribuido <span class="fw-bolder text-success">{{ $order->package->name }}</span>
                            @endif
                            @if ($order->type == 'Pacote Trocado')
                                Pacote trocado de <span class="fw-bolder text-danger">{{ $order->previousPackage->name }}</span> para <span class="fw-bolder text-success">{{ $order->package->name }}</span>.
                            @endif
                            @if ($order->type == 'Renovação')
                                {{ $order->type }} do plano <span class="fw-bolder text-success">{{ $order->package->name }}</span>
                            @endif
                        </span>
                        @if ($order->description)
                        <p class="text-gray-700 fs-8 mb-0"><b>Observação:</b> {{ $order->description }}</p>
                        @endif
                    </td>
                    <td class="text-start">
                        <span class="text-gray-700 fw-bold">R$ {{ number_format($order->total(), 2, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        @if ($order->status == 'Pago')
                            {{ $order->paidBy() }}
                        @else
                        <span class="badge badge-light text-muted">Não Pago</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($order->status == 'Pago')
                        <span class="badge badge-light-success">Pago</span>
                        @elseif ($order->status == 'Cancelado')
                        <span class="badge badge-light-danger">Cancelado</span>
                        @else
                            <span class="badge badge-light-warning">Pendente</span>
                            @if ($order->type == 'Renovação')
                            <a href="{{ route('subscriptions.renew', $order->id) }}" class="text-hover-primary">
                                <i class="fa-solid fa-arrow-rotate-right" data-bs-toggle="tooltip" data-bs-html="true" title="Forçar Renovação"></i>
                            </a>
                            @endif
                            @if ($order->type == 'Pacote Trocado')
                            <a href="{{ route('payments.approve', $order->id) }}" class="text-hover-primary">
                                <i class="fa-solid fa-sack-dollar" data-bs-toggle="tooltip" data-bs-html="true" title="Aprovar Pagamento"></i>
                            </a>
                            @endif
                        @endif
                    </td>
                </tr>
                @foreach ($order->transactions()->orderBy('created_at', 'DESC')->get() as $transaction)
                <tr class="text-muted bg-light">
                    <td class="px-2 text-gray-700 fw-bolder mb-0"></td>
                    <td class="text-gray-700 fw-semibold text-uppercase">
                        <span class="fs-9">Ref: </span><span class="text-primary fw-bold">OT{{ $transaction->id }}</span>
                    </td>
                    <td class="text-start py-1">
                        <span class="text-gray-700 lh-1">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                    </td>
                    <td class="p-0 text-center">
                        {{ $transaction->method }} <b class="fw-bolder text-success">{{ $transaction->gateway->name }}</b>
                    </td>
                    <td class="text-center">
                        @if ($transaction->status == 'Pago')
                        <span class="badge badge-light-success">Pago</span>
                        @elseif ($transaction->status == 'Cancelado')
                        <span class="badge badge-light-danger">Cancelado</span>
                        @elseif ($transaction->status == 'Falhou')
                        <span class="badge badge-light-danger">Falhou</span>
                        @else
                        <span class="badge badge-light-warning">Pendente</span>
                        @endif
                        @if ($transaction->response)
                            <i class="fa-solid fa-circle-info text-gray-400" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $transaction->response }}"></i>
                        @endif
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@section('modals')
    @parent
    <div class="modal fade" tabindex="-1" id="modal-order">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                {{-- RESULTS HERE --}}
                {{-- RESULTS HERE --}}
                {{-- RESULTS HERE --}}
            </div>
        </div>
    </div>
@endsection

@section('custom-footer')
    @parent
    <script>
    $(document).ready(function() {
        $(document).on('click', '.show-details', function(){

            // Obtém o ID do pedido
            var orderId = $(this).data('order');

            // Busca detalhes do pedido
            $.ajax({
                type:'GET',
                url: "{{ route('orders.show', '') }}/" + orderId,
                success: function(response) {
                    $('#modal-order .modal-content').html(response);
                    $('#modal-order').modal('show');
                },
            });

        });
    });
    </script>
@endsection