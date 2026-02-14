<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSuggestion;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SuggestionProcessingController extends Controller
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
        return IntegrationSuggestion::query();
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
                    ->orWhere('description', 'like', "%{$searchBy}%");
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
                'client_id' => 'client_id',
                'name' => 'name',
                'description' => 'description',
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
            ->addColumn('progress_badge', function ($suggestion) {
                return match ($suggestion->progress) {
                    'aberto' => '<span class="badge badge-light-warning">Aberto</span>',
                    'em andamento' => '<span class="badge badge-light-info">Em Andamento</span>',
                    default => '<span class="badge badge-light-danger">Fechado</span>',
                };
            })
            ->addColumn('status_label', function ($suggestion) {
                if ((int) $suggestion->status === 0) {
                    return '<span class="badge badge-light-danger">Desabilitado</span>';
                }
                return '<span class="badge badge-light-success">Habilitado</span>';
            })
            ->addColumn('actions', function ($suggestion) {
                $options = [
                    'aberto' => 'Aberto',
                    'em andamento' => 'Em Andamento',
                    'fechado' => 'Finalizado',
                ];

                $html = '<select name="progress" class="form-select form-select-solid js-suggestion-progress" data-id="' . $suggestion->id . '">';
                foreach ($options as $value => $label) {
                    $selected = $suggestion->progress === $value ? ' selected' : '';
                    $html .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
                }
                $html .= '</select>';

                return $html;
            })
            ->editColumn('created_at', function ($suggestion) {
                return $suggestion->created_at?->format('d/m/Y');
            })
            ->rawColumns(['progress_badge', 'status_label', 'actions'])
            ->make(true);
    }
}
