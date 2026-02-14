<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">ID</th>
                    <th class="w-150px text-center px-6">Pacote</th>
                    <th class="">Data Inicio</th>
                    <th class="">Data Fim</th>
                    <th class="w-100px text-center px-6">Transação</th>
                    <th class="">Status</th>
                </tr>
            </thead>
            <tbody class="text-start">
                @foreach ($client->subscriptions()->orderBy('created_at', 'DESC')->get() as $subscription)
                <tr>
                    <td class="text-start px-2">
                        <span class="text-gray-700 fw-bolder mb-0" title="{{ $subscription->created_at->format('d/m/Y H:i:s') }}">
                            #{{ str_pad($subscription->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <p class="text-gray-700 m-0 text-center">{{ $subscription->package->name }}</p>
                    </td>
                    <td>
                        <span class="text-gray-600">
                            {{ $subscription->start_date->format('d/m/Y') }}
                        </span>
                    </td>
                    <td>
                        <span class="text-gray-600">
                            {{ $subscription->end_date->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="text-gray-700 fw-bold">#{{ str_pad($subscription->order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <p class="text-gray-700 m-0 text-center">{{ $subscription->order->method }}</p>
                    </td>
                    <td>
                        @if ($subscription->status == 'Ativo')
                        <span class="badge badge-light-success">
                            Ativo
                        </span>
                        @elseif($subscription->status == 'Cancelado' || $subscription->status == 'Expirado')
                        <span class="badge badge-light-danger">
                            {{ $subscription->status }}
                        </span>
                        @else
                        <span class="badge badge-light-info">
                            {{ $subscription->status }}
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>