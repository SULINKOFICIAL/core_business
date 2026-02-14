<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderProcessingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function process(Request $request)
    {
        // Extrai dados
        $data = $request->all();

        // Incia consulta
        $query = $this->loadTables();

        // Filtra dados relevantes
        $query = $this->filters($query, $data);

        // Filtra pela busca
        $query = $this->search($query, $data);

        // Ordena resultados
        $query = $this->ordering($query, $data);

        // Retorna dados
        return $this->formatResults($query);
    }

    /**
     * Inicializa a consulta com junções e seleção de colunas.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function loadTables()
    {
        return Order::with(['client', 'items']);
    }

    /**
     * Inicializa a consulta com junções e seleção de colunas.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        if (!empty($data['order_status']) && $data['order_status'] !== 'all') {
            $query->where('status', $data['order_status']);
        }

        return $query;
    }

    /**
     * Aplica filtros de pesquisa à consulta.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Query\Builder
     */
    public function search($query, $data)
    {
        $searchBy = $data['searchBy'] ?? ($data['search']['value'] ?? null);

        if (!empty($searchBy)) {
            $query->where(function ($sub) use ($searchBy) {
                $sub->where('id', 'like', "%{$searchBy}%")
                    ->orWhere('status', 'like', "%{$searchBy}%")
                    ->orWhereHas('client', function ($clientQuery) use ($searchBy) {
                        $clientQuery->where('name', 'like', "%{$searchBy}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Aplica a ordenação à consulta.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Query\Builder
     */
    public function ordering($query, $data)
    {
        if (!empty($data['order'])) {
            $direction = $data['order'][0]['dir'];
            $index = $data['order'][0]['column'] ?? 0;
            $orderThis = $data['order_by'] ?? ($data['columns'][$index]['data'] ?? 'created_at');

            $column = match ($orderThis) {
                'id' => 'id',
                'type' => 'type',
                'created_at' => 'created_at',
                'paid_at' => 'paid_at',
                default => 'created_at',
            };

            return $query->orderBy($column, $direction);
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Aplica a ordenação à consulta.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Query\Builder
     */
    public function formatResults($query)
    {
        return DataTables::eloquent($query)
            ->addColumn('order_label', function ($order) {
                return '<span class="fw-bolder text-gray-700">#' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . '</span>';
            })
            ->addColumn('client_name', function ($order) {
                if (!$order->client) {
                    return '<span class="text-muted">N/A</span>';
                }
                return '<a href="' . route('clients.show', $order->client->id) . '" class="text-gray-700 text-hover-primary fw-bold">' . e($order->client->name) . '</a>';
            })
            ->editColumn('type', function ($order) {
                return '<span class="text-gray-600">' . e($order->type ?? '—') . '</span>';
            })
            ->addColumn('status_label', function ($order) {
                $status = strtolower((string) ($order->status ?? ''));
                if (in_array($status, ['paid', 'pago'], true)) {
                    return '<span class="badge badge-light-success">Pago</span>';
                }
                if (in_array($status, ['canceled', 'cancelado'], true)) {
                    return '<span class="badge badge-light-danger">Cancelado</span>';
                }
                if (in_array($status, ['draft', 'rascunho'], true)) {
                    return '<span class="badge badge-light-secondary">Rascunho</span>';
                }
                if (in_array($status, ['pending_payment', 'pendente'], true)) {
                    return '<span class="badge badge-light-warning">Pendente</span>';
                }
                return '<span class="badge badge-light-info">' . e($order->status ?? '—') . '</span>';
            })
            ->addColumn('items_count', function ($order) {
                return '<span class="text-gray-700">' . $order->items->count() . '</span>';
            })
            ->addColumn('total_label', function ($order) {
                return '<span class="text-gray-700 fw-bold">R$ ' . number_format($order->total(), 2, ',', '.') . '</span>';
            })
            ->editColumn('created_at', function ($order) {
                return '<span class="text-gray-600">' . ($order->created_at?->format('d/m/Y H:i') ?? '—') . '</span>';
            })
            ->editColumn('paid_at', function ($order) {
                return '<span class="text-gray-600">' . ($order->paid_at?->format('d/m/Y H:i') ?? '—') . '</span>';
            })
            ->addColumn('actions', function ($order) {
                return '<a href="' . route('orders.show', $order->id) . '" class="btn btn-sm btn-primary btn-active-success">Detalhes</a>';
            })
            ->rawColumns(['order_label', 'client_name', 'type', 'status_label', 'items_count', 'total_label', 'created_at', 'paid_at', 'actions'])
            ->make(true);
    }
}
