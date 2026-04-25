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
            <a href="{{ route('orders.reprocess.subscription', $order->id) }}" class="btn btn-sm btn-light">Reprocessar assinatura pagarme</a>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light">Voltar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4 mb-6">
            <div class="col-md-3">
                <div class="text-muted">Cliente</div>
                <div class="fw-bold text-gray-800">
                    {{ $order->tenant?->name ?? 'N/A' }}
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
                <div class="fw-bold text-gray-800">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Pago em</div>
                <div class="text-gray-700">{{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}</div>
            </div>
            <div class="col-md-12">
                <div class="text-muted">Assinatura:</div>
                <div class="row bg-light rounded p-3">
                    <div class="col-md-3">
                        <div class="text-muted">Id Externo</div>
                        <div class="fw-bold text-gray-800">
                            # {{ $order->subscription->pagarme_subscription_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Id Externo Cartão</div>
                        <div class="fw-bold text-gray-800">
                            # {{ $order->subscription->pagarme_card_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Intervalo</div>
                        <div class="fw-bold text-gray-800">
                            @if ($order->subscription->interval == 'month')
                                Mensal
                            @elseif ($order->subscription->interval == 'quarter')
                                Trimestral
                            @elseif ($order->subscription->interval == 'semester')
                                Semestral
                            @elseif ($order->subscription->interval == 'year')
                                Anual
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Método de Pagamento</div>
                        <div class="fw-bold text-gray-800">
                            @if($order->subscription->payment_method == 'credit_card')
                                Cartão de Crédito
                            @elseif($order->subscription->payment_method == 'debit_card')
                                Cartão de Débito
                            @elseif($order->subscription->payment_method == 'pix')
                                Pix
                            @elseif($order->subscription->payment_method == 'boleto')
                                Boleto
                            @elseif($order->subscription->payment_method == 'liberado')
                                Liberado à Parte
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Moeda</div>
                        <div class="fw-bold text-gray-800">
                            {{ $order->subscription->currency }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Parcelas</div>
                        <div class="fw-bold text-gray-800">
                            @if($order->subscription->payment_method == 'liberado' && $order->subscription->installments == 1)
                                Liberado à Parte
                            @elseif($order->subscription->installments == 1 && $order->subscription->payment_method != 'liberado')
                                À vista
                            @else
                                {{ $order->subscription->installments }}x
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Status</div>
                        <div class="fw-bold text-gray-800">
                            @if($order->subscription->status == 'paid')
                                Pago
                            @elseif($order->subscription->status == 'active')
                                Ativo
                            @elseif($order->subscription->status == 'canceled')
                                Cancelado
                            @elseif($order->subscription->status == 'pending')
                                Pendente
                            @elseif ($order->subscription->status == 'failed')
                                Falhou
                            @elseif ($order->subscription->status == 'refunded')
                                Estornado
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="text-muted">Ciclos:</div>
                <div class="row bg-light rounded p-3">
                    @foreach($order->subscription->cycles as $cycle)
                        <div class="col-md-3">
                            <div class="text-muted">Id Externo</div>
                            <div class="fw-bold text-gray-800">
                                # {{ $cycle->pagarme_cycle_id }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Data de Pagamento</div>
                            <div class="fw-bold text-gray-800">
                                {{ $cycle->billing_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Data de Vencimento</div>
                            <div class="fw-bold text-gray-800">
                                {{ $cycle->next_billing_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Status</div>
                            <div class="fw-bold text-gray-800">
                                @if($cycle->status == 'billed')
                                    Pago
                                @elseif($cycle->status == 'pending')
                                    Pendente
                                @elseif($cycle->status == 'failed')
                                    Falhou
                                @elseif($cycle->status == 'refunded')
                                    Estornado
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Ciclo</div>
                            <div class="fw-bold text-gray-800">
                                {{ $cycle->cycle }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Data Inicial</div>
                            <div class="fw-bold text-gray-800">
                                {{ $cycle->start_date->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted">Data Final</div>
                            <div class="fw-bold text-gray-800">
                                {{ $cycle->end_date->format('d/m/Y') }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="col-12">
                                <hr>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <h5 class="mb-3">Itens</h5>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th>Nome</th>
                    <th>Quantidade</th>
                    <th>Tipo</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->plan->items as $item)
                <tr>
                    <td>{{ $item->module_name }}</td>
                    <td>1</td>
                    <td>{{ $item->billing_type }}</td>
                    <td>R$ {{ number_format($item->module_value, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h5 class="mt-6 mb-3">Transações</h5>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th>ID</th>
                    <th>Status</th>
                    <th>Método</th>
                    <th>Gateway</th>
                    <th>Valor</th>
                    <th>Pago as</th>
                    <th>Recorrência</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->transactions as $transaction)
                    <tr>
                        <td>#{{ $transaction->id }}</td>
                        <td>
                            @if($transaction->status == 'paid')
                                <span class="badge badge-success">Pago</span>
                            @elseif($transaction->status == 'pending')
                                <span class="badge badge-warning">Pendente</span>
                            @elseif($transaction->status == 'failed')
                                <span class="badge badge-danger">Falhou</span>
                            @elseif($transaction->status == 'refunded')
                                Estornado
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($transaction->method == 'credit_card')
                                Cartão de Crédito
                            @elseif($transaction->method == 'debit_card')
                                Cartão de Débito
                            @elseif($transaction->method == 'pix')
                                PIX
                            @elseif($transaction->method == 'boleto')
                                Boleto
                            @elseif($transaction->method == 'liberado')
                                Liberado à Parte
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($transaction->gateway?->name == 'pagarme')
                                Pagar.me
                            @elseif($transaction->gateway?->name == 'stripe')
                                Stripe
                            @else
                                —
                            @endif
                        </td>
                        <td>R$ {{ number_format($transaction->amount ?? 0, 2, ',', '.') }}</td>
                        <td>
                            @if($transaction->paid_at)
                                {{ $transaction->paid_at->format('d/m/Y H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($transaction->recurrency == 'first')
                                Primeira
                            @else
                                Recorrente
                            @endif
                        </td>
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
