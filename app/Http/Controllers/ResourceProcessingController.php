<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ResourceProcessingController extends Controller
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
        return Resource::query();
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
            $query->where(function ($sub) use ($searchBy) {
                $sub->where('name', 'like', "%{$searchBy}%")
                    ->orWhere('slug', 'like', "%{$searchBy}%");
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
                'name' => 'name',
                'slug' => 'slug',
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
            ->editColumn('name', function ($resource) {
                return '<a href="' . route('resources.edit', $resource->id) . '" class="text-gray-700 text-hover-primary">' . e($resource->name) . '</a>';
            })
            ->editColumn('created_at', function ($resource) {
                return '<span class="text-center d-block">' . $resource->created_at?->format('d/m/Y') . '</span>';
            })
            ->addColumn('status_label', function ($resource) {
                if ((int) $resource->status === 0) {
                    return '<span class="badge badge-light-danger">Desabilitado</span>';
                }

                return '<span class="badge badge-light-success">Habilitado</span>';
            })
            ->addColumn('actions', function ($resource) {
                return '<a href="' . route('resources.edit', $resource->id) . '" class="text-gray-600 w-45px" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>';
            })
            ->rawColumns(['name', 'created_at', 'status_label', 'actions'])
            ->make(true);
    }
}
