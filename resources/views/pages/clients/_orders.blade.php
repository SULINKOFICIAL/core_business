<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="w-150px text-start">ID</th>
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
                            <span class="text-gray-700 fw-bold">R$ {{ number_format($order->total(), 2, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            @if ($order->method == 'credit_card')
                                <span class="fw-bolder text-success">
                                    Cartão de Crédito
                                </span>
                            @else
                            <span class="badge badge-light text-muted">Não Pago</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($order->status == 'paid')
                            <span class="badge badge-light-success">Pago</span>
                            @elseif ($order->status == 'pending')
                            <span class="badge badge-light-warning">Pendente</span>
                            @elseif ($order->status == 'canceled')
                            <span class="badge badge-light-danger">Cancelado</span>
                            @elseif ($order->status == 'draft')
                            <span class="badge badge-light-warning">Rascunho</span>
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