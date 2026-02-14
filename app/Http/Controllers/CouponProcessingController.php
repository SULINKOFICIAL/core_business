<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CouponProcessingController extends Controller
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
        return Coupon::query();
    }

    /**
     * Inicializa a consulta com junções e seleção de colunas.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        if (!empty($data['coupon_type']) && $data['coupon_type'] !== 'all') {
            $query->where('type', $data['coupon_type']);
        }

        if (isset($data['client_status']) && $data['client_status'] !== 'all') {
            $query->where('is_active', (int) $data['client_status']);
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
                $sub->where('code', 'like', "%{$searchBy}%")
                    ->orWhere('type', 'like', "%{$searchBy}%");
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
            $orderThis = $data['order_by'] ?? ($data['columns'][$index]['data'] ?? 'code');

            $column = match ($orderThis) {
                'code' => 'code',
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
            ->editColumn('code', function ($coupon) {
                return '<span class="fw-bolder text-gray-700">' . e($coupon->code) . '</span>';
            })
            ->addColumn('type_label', function ($coupon) {
                return match ($coupon->type) {
                    'percent' => '<span class="badge badge-light-primary">Percentual</span>',
                    'fixed' => '<span class="badge badge-light-success">Valor fixo</span>',
                    default => '<span class="badge badge-light-warning">Trial</span>',
                };
            })
            ->addColumn('amount_label', function ($coupon) {
                if ($coupon->type === 'trial') {
                    return '<span class="text-gray-700">' . ($coupon->trial_months ?? 1) . ' mês(es)</span>';
                }

                if ($coupon->type === 'percent') {
                    return '<span class="text-gray-700">' . number_format($coupon->amount ?? 0, 2, ',', '.') . '%</span>';
                }

                return '<span class="text-gray-700">R$ ' . number_format($coupon->amount ?? 0, 2, ',', '.') . '</span>';
            })
            ->addColumn('validity', function ($coupon) {
                $start = $coupon->starts_at?->format('d/m/Y') ?? '—';
                $end = $coupon->ends_at?->format('d/m/Y') ?? '—';
                return '<span class="text-gray-600">' . $start . ' → ' . $end . '</span>';
            })
            ->addColumn('uses', function ($coupon) {
                $text = (string) ($coupon->redeemed_count ?? 0);
                if (!empty($coupon->max_redemptions)) {
                    $text .= ' / ' . $coupon->max_redemptions;
                }
                return '<span class="text-gray-700">' . $text . '</span>';
            })
            ->addColumn('status_label', function ($coupon) {
                if ($coupon->is_active) {
                    return '<span class="badge badge-light-success">Ativo</span>';
                }
                return '<span class="badge badge-light-danger">Inativo</span>';
            })
            ->addColumn('actions', function ($coupon) {
                $toggle = $coupon->is_active ? 'Desabilitar' : 'Ativar';
                return '<a href="' . route('coupons.edit', $coupon->id) . '" class="btn btn-sm btn-primary btn-active-success me-2">Editar</a>'
                    . '<a href="' . route('coupons.destroy', $coupon->id) . '" class="btn btn-sm btn-light">' . $toggle . '</a>';
            })
            ->rawColumns(['code', 'type_label', 'amount_label', 'validity', 'uses', 'status_label', 'actions'])
            ->make(true);
    }
}
