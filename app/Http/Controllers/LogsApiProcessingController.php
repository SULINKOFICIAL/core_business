<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LogsApiProcessingController extends Controller
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
        // Tabela principal
        $query = DB::table('logs_apis');

        // Retorna consulta
        return $query;
    }

    /**
     * Inicializa a consulta com junções e seleção de colunas.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        // Filtra por status (ativo/inativo)
        if (isset($data['client_status']) && $data['client_status'] !== 'all') {
            $query->where('logs_apis.status', (int) $data['client_status']);
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
        // Obtém dados da consulta
        $searchBy = $data['searchBy'] ?? null;

        // Realiza filtro na busca
        if ($searchBy != '') {
            // Realiza busca em campos principais
            $query->where(function ($sub) use ($searchBy) {
                $sub->where('logs_apis.api', 'like', "%$searchBy%")
                    ->orWhere('logs_apis.id', 'like', "%$searchBy%")
                    ->orWhere('logs_apis.new_log_id', 'like', "%$searchBy%");
            });
        }

        // Retorna a query
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
        // Ordena de acordo com a coluna desejada
        if (!empty($data['order'])) {
            // Direção e coluna
            $direction = $data['order'][0]['dir'];
            $orderThis = $data['order_by'];

            // Define qual a lógica de ordenação
            switch ($orderThis) {
                case 'id':
                    $column = 'logs_apis.id';
                    break;

                case 'api':
                    $column = 'logs_apis.api';
                    break;

                case 'created_at':
                    $column = 'logs_apis.created_at';
                    break;

                case 'status':
                    $column = 'logs_apis.status';
                    break;

                default:
                    $column = 'logs_apis.id';
                    break;
            }

            // Ordena a coluna
            return $query->orderBy($column, $direction);
        }

        return $query->orderByDesc('logs_apis.id');
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
        // SELECT final
        $query->select(
            'logs_apis.id as id',
            'logs_apis.api as api',
            'logs_apis.json as json',
            'logs_apis.reprocessed as reprocessed',
            'logs_apis.new_log_id as new_log_id',
            'logs_apis.status as status',
            'logs_apis.created_at as created_at'
        );

        return DataTables::query($query)
            ->addColumn('api', function ($row) {
                return '<span class="fw-bolder text-gray-700">' . e($row->api ?? '-') . '</span>';
            })
            ->addColumn('json', function ($row) {
                $content = (string) ($row->json ?? '');
                $short = mb_strlen($content) > 80 ? mb_substr($content, 0, 80) . '...' : $content;
                return '<span class="text-gray-600" title="' . e($content) . '">' . e($short) . '</span>';
            })
            ->addColumn('reprocessed', function ($row) {
                return (int) $row->reprocessed === 1
                    ? '<span class="badge badge-light-info">Sim</span>'
                    : '<span class="badge badge-light">Não</span>';
            })
            ->addColumn('new_log_id', function ($row) {
                return '<span class="text-gray-700">' . e($row->new_log_id ?? '-') . '</span>';
            })
            ->addColumn('status', function ($row) {
                return (int) $row->status === 1
                    ? '<span class="badge badge-light-success">Ativo</span>'
                    : '<span class="badge badge-light-danger">Inativo</span>';
            })
            ->addColumn('created_at', function ($row) {
                return '<span class="text-gray-600">' . ($row->created_at ? date('d/m/Y H:i', strtotime($row->created_at)) : '-') . '</span>';
            })
            ->rawColumns(['api', 'json', 'reprocessed', 'new_log_id', 'status', 'created_at'])
            ->make(true);
    }
}
