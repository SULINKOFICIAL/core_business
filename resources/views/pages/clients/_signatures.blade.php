<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="w-150px text-start">ID</th>
                    <th class="w-150px text-center px-6">Transação</th>
                    <th class="">Data Inicio</th>
                    <th class="">Data Fim</th>
                    <th class="">Status</th>
                </tr>
            </thead>
            <tbody class="text-start">
                @foreach ($client->subscriptions()->orderBy('created_at', 'DESC')->get() as $subscription)
                <tr>
                    <td class="w-100px text-start px-2">
                        <p class="text-gray-700 fw-bolder mb-0">
                            #{{ str_pad($subscription->id, 4, '0', STR_PAD_LEFT) }}
                        </p>
                        <span class="text-gray-600 fs-8">
                            {{ $subscription->created_at->format('d/m/Y') }} às {{ $subscription->created_at->format('H:i:s') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="text-gray-700 fw-bold">#{{ str_pad($subscription->order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <p class="text-gray-700 m-0 text-center">{{ $subscription->order->method }}</p>
                    </td>
                    <td>
                        <span class="text-gray-600">
                            {{ $subscription->start_date->format('d/m/Y') }} às {{ $subscription->start_date->format('H:i:s') }}
                        </span>
                    </td>
                    <td>
                        <span class="text-gray-600">
                            {{ $subscription->end_date->format('d/m/Y') }} às {{ $subscription->end_date->format('H:i:s') }}
                        </span>
                    </td>
                    <td>
                        @if ($subscription->status == 'Ativa')
                        <span class="badge badge-light-success">
                            Ativa
                        </span>
                        @else
                        <span class="badge badge-light-danger">
                            Cancelada
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>