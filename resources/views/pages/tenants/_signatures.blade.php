<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">Plano</th>
                    <th class="text-center">Valor</th>
                    <th class="text-center">Usuários</th>
                    <th class="text-center">Armazenamento</th>
                    <th class="text-center">Último Pedido</th>
                    <th class="text-center">Período</th>
                    <th class="text-center">Origem</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-start">
                @forelse ($plansHistory as $plan)
                    @php
                        $lastOrder = $plan->orders->sortByDesc('id')->first();
                        $lastCycle = $plan->subscription?->cycles?->sortByDesc('id')->first();
                        $isActive = (int) $plan->status === 1;
                        $sourceMethod = $plan->subscription?->payment_method ?? $lastOrder?->method;
                        $isManual = $sourceMethod === 'manual_admin';
                    @endphp
                    <tr>
                        <td class="text-start px-2">
                            <span class="text-gray-700 fw-bolder mb-0" title="{{ $plan->created_at?->format('d/m/Y H:i:s') }}">
                                #{{ str_pad($plan->id, 4, '0', STR_PAD_LEFT) }} - {{ $plan->name }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-gray-700">R$ {{ number_format((float) $plan->value, 2, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-gray-700">{{ (int) $plan->users_limit }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-gray-700">{{ number_format(((int) $plan->size_storage) / 1073741824, 2, ',', '.') }} GB</span>
                        </td>
                        <td class="text-center">
                            @if ($lastOrder)
                                <span class="text-gray-700 fw-bold">#{{ str_pad($lastOrder->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <p class="text-gray-600 m-0">{{ $lastOrder->created_at?->format('d/m/Y H:i') }}</p>
                            @else
                                <span class="text-muted">Sem pedido</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($lastCycle && $lastCycle->start_date && $lastCycle->end_date)
                                <span class="text-gray-700">{{ $lastCycle->start_date->format('d/m/Y') }} - {{ $lastCycle->end_date->format('d/m/Y') }}</span>
                            @else
                                <span class="text-muted">Sem ciclo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($isManual)
                                <span class="badge badge-light-warning">Manual</span>
                            @else
                                <span class="badge badge-light-info">Automatizado</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($isActive)
                                <span class="badge badge-light-success">Ativo</span>
                            @else
                                <span class="badge badge-light-secondary">Inativo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-6">Nenhum histórico de plano encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
