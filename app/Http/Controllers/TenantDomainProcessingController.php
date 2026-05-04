<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TenantDomainProcessingController extends Controller
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
        return DB::table('tenants_domains')
            ->leftJoin('tenants', 'tenants.id', '=', 'tenants_domains.tenant_id')
            ->select([
                'tenants_domains.id',
                'tenants_domains.tenant_id',
                'tenants_domains.domain',
                'tenants_domains.description',
                'tenants_domains.status',
                'tenants_domains.auto_generate',
                'tenants_domains.created_at',
                'tenants_domains.updated_at',
                'tenants.name as tenant_name',
            ]);
    }

    /**
     * Aplica os filtros estruturados informados na listagem.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function filters($query, $data)
    {
        // Filtra por status (ativo/inativo)
        if (isset($data['status_filter']) && $data['status_filter'] !== 'all') {
            $query->where('tenants_domains.status', (int) $data['status_filter']);
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
        $searchBy = trim((string) ($data['searchBy'] ?? ''));

        // Realiza filtro na busca
        if ($searchBy !== '') {

            // Realiza busca por domínio, descrição, tenant id e nome do tenant
            $query->where(function ($subQuery) use ($searchBy) {
                $subQuery->where('tenants_domains.domain', 'like', "%{$searchBy}%")
                    ->orWhere('tenants_domains.description', 'like', "%{$searchBy}%")
                    ->orWhere('tenants_domains.tenant_id', 'like', "%{$searchBy}%")
                    ->orWhere('tenants.name', 'like', "%{$searchBy}%");
            });

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
            $direction = $data['order'][0]['dir'] ?? 'desc';
            $orderThis = $data['order_by'] ?? 'id';

            // Define qual a lógica de ordenação
            switch ($orderThis) {
                case 'domain':
                    $column = 'tenants_domains.domain';
                    break;

                case 'tenant':
                    $column = 'tenants.name';
                    break;

                case 'status':
                    $column = 'tenants_domains.status';
                    break;

                case 'updated_at':
                    $column = 'tenants_domains.updated_at';
                    break;

                default:
                    $column = 'tenants_domains.id';
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
        return DataTables::query($query)
            ->addColumn('id', function ($row) {
                return "<span class='text-gray-700 fw-bold'>#" . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT) . "</span>";
            })
            ->addColumn('domain', function ($row) {
                return "<a href='https://" . e((string) $row->domain) . "' target='_blank' class='text-gray-700 text-hover-primary fw-bold'>" . e((string) $row->domain) . "</a>";
            })
            ->addColumn('tenant', function ($row) {
                $tenantLabel = trim((string) ($row->tenant_name ?? ''));
                $tenantId = (int) ($row->tenant_id ?? 0);

                if ($tenantId <= 0) {
                    return "<span class='text-gray-500'>Sem tenant</span>";
                }

                if ($tenantLabel === '') {
                    $tenantLabel = 'Tenant #' . $tenantId;
                }

                return "<a href='" . route('tenants.show', $tenantId) . "' class='text-gray-700 text-hover-primary fw-semibold'>" . e($tenantLabel) . "</a>";
            })
            ->addColumn('status', function ($row) {
                return (int) $row->status === 1
                    ? "<span class='badge badge-light-success'>Ativo</span>"
                    : "<span class='badge badge-light-danger'>Inativo</span>";
            })
            ->addColumn('updated_at', function ($row) {
                $date = $row->updated_at ?? $row->created_at;
                if (empty($date)) {
                    return "<span class='text-gray-500'>-</span>";
                }

                return "<span class='text-gray-700'>" . date('d/m/Y H:i', strtotime((string) $date)) . "</span>";
            })
            ->rawColumns(['id', 'domain', 'tenant', 'status', 'updated_at'])
            ->make(true);
    }
}
