<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ModuleCategoryProcessingController extends Controller
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
        return ModuleCategory::query();
    }

    /**
     * Inicializa a consulta com junções e seleção de colunas.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        if (isset($data['client_status']) && $data['client_status'] !== 'all') {
            $query->where('status', (int) $data['client_status']);
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
            $query->where('name', 'like', "%{$searchBy}%");
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
                'name' => 'name',
                'created_at' => 'created_at',
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
            ->addColumn('status_label', function ($category) {
                if ((int) $category->status === 0) {
                    return '<span class="badge badge-light-danger">Desabilitado</span>';
                }
                return '<span class="badge badge-light-success">Habilitado</span>';
            })
            ->editColumn('created_at', function ($category) {
                return '<span class="text-gray-600">' . $category->created_at?->format('d/m/Y') . '</span>';
            })
            ->addColumn('actions', function ($category) {
                return '<div class="d-flex gap-4 align-items-center justify-content-center">'
                    . '<a href="' . route('modules.categories.edit', $category->id) . '" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">Editar</a>'
                    . '</div>';
            })
            ->rawColumns(['status_label', 'created_at', 'actions'])
            ->make(true);
    }
}
