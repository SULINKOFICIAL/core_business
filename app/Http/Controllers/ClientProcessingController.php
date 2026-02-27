<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ClientProcessingController extends Controller
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

        // Inicia consulta
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
        return DB::table('clients');
    }

    /**
     * Aplica os filtros estruturados informados na listagem.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        // Filtra por tipo de instalação
        if (!empty($data['payment_type']) && $data['payment_type'] !== 'all') {
            $query->where('clients.type_installation', $data['payment_type']);
        }

        // Filtra por status (ativo/inativo)
        if (isset($data['client_status']) && $data['client_status'] !== 'all') {
            $query->where('clients.status', (int) $data['client_status']);
        }

        // Mantém compatibilidade com filtro antigo por array de status
        if (isset($data['status']) && is_array($data['status']) && !empty($data['status'])) {
            $query->whereIn('clients.status', $data['status']);
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

            // Realiza busca no nome
            $query->where('clients.name', 'like', "%$searchBy%");
        
        }

        // Retorna consulta filtrada
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
                case 'name':
                    $column = 'clients.name';
                    break;

                case 'created_at':
                    $column = 'clients.created_at';
                    break;

                case 'status':
                    $column = 'clients.status';
                    break;

                default:
                    $column = 'clients.id';
                    break;
            }

            // Ordena a coluna
            return $query->orderBy($column, $direction);
        }

        // Mantém ordenação padrão por id quando não há ordenação explícita
        return $query;
    }

    /**
     * Formata os resultados para o DataTables.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Query\Builder
     */
    public function formatResults($query)
    {
        // SELECT final
        $query->select(
            'clients.id                as id',
            'clients.name              as name',
            'clients.type_installation as type_installation',
            'clients.created_at        as created_at',
            'clients.db_last_version   as db_last_version',
            'clients.db_error          as db_error',
            'clients.git_last_version  as git_last_version',
            'clients.git_error         as git_error',
            'clients.status            as status',
            'clients.token             as token'
        );

        $query->selectSub(
            DB::table('clients_domains')
                ->select('domain')
                ->whereColumn('clients_domains.client_id', 'clients.id')
                ->orderBy('clients_domains.id')
                ->limit(1),
            'first_domain'
        );

        $query->selectSub(
            DB::table('clients_domains')
                ->selectRaw('COUNT(*)')
                ->whereColumn('clients_domains.client_id', 'clients.id'),
            'domains_count'
        );

        return DataTables::query($query)
            ->addColumn('name', function ($row) {
                $html = '<a href="' . route('clients.show', $row->id) . '" class="text-gray-700 text-hover-primary fw-bold">'
                    . e($row->name)
                    . '</a><br>';

                if ((int) $row->domains_count > 0 && !empty($row->first_domain)) {
                    $html .= '<a href="https://' . e($row->first_domain) . '" target="_blank" class="text-gray-600 text-hover-primary m-0 text-center">'
                        . e($row->first_domain)
                        . '</a>';

                    if ((int) $row->domains_count > 1) {
                        $html .= ' <i class="fa-solid fa-circle-plus text-gray-500 fs-9"></i>';
                    }
                } else {
                    $html .= '<span class="badge badge-light-danger">Sem domínio</span>';
                }

                return $html;
            })

            ->addColumn('type', function ($row) {
                return match ($row->type_installation) {
                    'shared' => '<span class="badge badge-success">Compartilhada</span>',
                    'dedicated' => '<span class="badge badge-info">Dedicada</span>',
                    default => '<span class="badge badge-secondary">Desconhecido</span>',
                };
            })

            ->addColumn('expires_at', function ($row) {
                return '<span class="text-gray-600">' . date('d/m/Y', strtotime($row->created_at)) . '</span>';
            })

            ->addColumn('bank', function ($row) {
                if ((int) $row->db_last_version === 0) {
                    return '<i class="fa-solid fa-circle-xmark text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($row->db_error ?? 'Banco de dados desatualizado') . '"></i>';
                }

                return '<i class="fa-solid fa-circle-check text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Banco de dados atualizado"></i>';
            })

            ->addColumn('git', function ($row) {
                if ((int) $row->git_last_version === 0) {
                    return '<i class="fa-solid fa-circle-xmark text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($row->git_error ?? 'Git desatualizado') . '"></i>';
                }

                return '<i class="fa-solid fa-circle-check text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Git atualizado"></i>';
            })

            ->addColumn('status', function ($row) {
                if ((int) $row->status === 0) {
                    return '<span class="badge badge-light-danger">Desabilitado</span>';
                }

                return '<span class="badge badge-light-success">Habilitado</span>';
            })

            ->addColumn('actions', function ($row) {
                $toggleText = (int) $row->status === 0 ? 'Ativar' : 'Desativar';

                $html = '<div class="d-flex gap-4 align-items-center">';
                $html .= '<a href="' . route('clients.show', $row->id) . '" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">Visualizar</a>';
                $html .= '<a href="#" class="btn btn-light-primary btn-active-light-primary btn-sm" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">';
                $html .= '<i class="fa-solid fa-ellipsis-vertical p-0"></i>';
                $html .= '</a>';
                $html .= '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-200px py-4" data-kt-menu="true">';
                if ((int) $row->domains_count > 0 && !empty($row->first_domain) && !empty($row->token)) {
                    $html .= '<div class="menu-item px-3">';
                    $html .= '<a href="https://' . e($row->first_domain) . '/acessar/' . e($row->token) . '" target="_blank" class="menu-link px-3" data-kt-docs-table-filter="delete_row">';
                    $html .= '<i class="fa-solid fa-globe me-2"></i>Acessar como sistema</a>';
                    $html .= '</div>';
                }
                $html .= '<div class="menu-item px-3"><a href="' . route('systems.update.database', $row->id) . '" class="menu-link px-3"><i class="fa-solid fa-database me-2"></i>Atualiza banco de dados</a></div>';
                $html .= '<div class="menu-item px-3"><a href="' . route('systems.update.git', $row->id) . '" class="menu-link px-3"><i class="fa-solid fa-code me-2"></i>Atualiza git</a></div>';
                $html .= '<div class="menu-item px-3"><a href="' . route('systems.run.scheduled.now.client', $row->id) . '" class="menu-link px-3"><i class="fa-solid fa-list-check me-2"></i>Executar Tarefas</a></div>';
                $html .= '<div class="menu-item px-3"><a href="' . route('clients.destroy', $row->id) . '" class="menu-link px-3"><i class="fa-solid fa-toggle-off me-2"></i>' . $toggleText . '</a></div>';

                $html .= '</div></div>';

                return $html;
            })

            ->rawColumns(['name', 'type', 'expires_at', 'bank', 'git', 'status', 'actions'])
            ->make(true);
    }
}
