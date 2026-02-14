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
        $query = DB::table('logs_apis')
            ->leftJoin('clients', 'clients.id', '=', 'logs_apis.client_id');

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
                    ->orWhere('logs_apis.new_log_id', 'like', "%$searchBy%")
                    ->orWhere('logs_apis.client_id', 'like', "%$searchBy%")
                    ->orWhere('clients.name', 'like', "%$searchBy%");
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

                case 'client':
                    $column = 'clients.name';
                    break;

                case 'created_at':
                    $column = 'logs_apis.created_at';
                    break;

                case 'dispatched_at':
                    $column = 'logs_apis.dispatched_at';
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
            'logs_apis.client_id as client_id',
            'clients.name as client_name',
            'logs_apis.json as json',
            'logs_apis.reprocessed as reprocessed',
            'logs_apis.new_log_id as new_log_id',
            'logs_apis.status as status',
            'logs_apis.dispatched_at as dispatched_at',
            'logs_apis.created_at as created_at'
        );

        return DataTables::query($query)
            ->addColumn('id', function ($row) {
                $api = '<span class="fw-bolder text-gray-700">' . e($row->id) . '</span>';
                $created_at = '<span class="text-gray-600 fs-8">' . ($row->created_at ? date('d/m/Y H:i', strtotime($row->created_at)) : '-') . '</span>';
                return $api . '<br>' . $created_at;
            })
            ->addColumn('api', function ($row) {
                $api = '<span class="fw-bolder text-gray-700">' . e($row->api ?? '-') . '</span>';
                return $api;
            })
            ->addColumn('json', function ($row) {
                $content = (string) ($row->json ?? '');
                $short = mb_strlen($content) > 80 ? mb_substr($content, 0, 60) . '...' : $content;
                return '<span class="text-gray-600 text-hover-primary cursor-pointer open-json" data-json="' . $row->id . '" title="' . e($content) . '">' . e($short) . '</span>';
            })
            ->addColumn('client', function ($row) {
                if (!empty($row->client_name)) {
                    return '<a href="' . route('clients.show', $row->client_id) . '" class="text-gray-700 text-hover-primary fw-bolder">' . e($row->client_name) . ' <span class="text-gray-500 fw-normal fs-8">#' . e($row->client_id) . '</span></a>';
                }
                return '<span class="text-gray-500">-</span>';
            })
            ->addColumn('reprocessed', function ($row) {
                return (int) $row->reprocessed === 1
                    ? '<span class="badge badge-light-info">Sim</span>'
                    : '<span class="badge badge-light">Não</span>';
            })
            ->addColumn('status', function ($row) {
                return match ((string) $row->status) {
                    'Processado' => '<span class="badge badge-light-success">Processado</span>',
                    'Aguardando' => '<span class="badge badge-light-warning">Aguardando</span>',
                    'Erro' => '<span class="badge badge-light-danger">Erro</span>',
                    default => '<span class="badge badge-light-info">'.$row->status.'</span>',
                };
            })
            ->addColumn('dispatched_at', function ($row) {
                if (empty($row->dispatched_at)) {
                    return '<span class="text-gray-500">-</span>';
                }

                return '<span class="text-gray-600">' . date('d/m/Y H:i', strtotime($row->dispatched_at)) . '</span>';
            })
            ->rawColumns(['id', 'api', 'json', 'client', 'reprocessed', 'new_log_id', 'status', 'dispatched_at'])
            ->make(true);
    }
}
