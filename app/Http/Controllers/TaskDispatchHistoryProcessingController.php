<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class TaskDispatchHistoryProcessingController extends Controller
{
    /**
     * Processa a listagem do histórico de tarefas para o DataTable.
     * Aplica filtros, busca e ordenação antes de formatar a resposta.
     */
    public function process(Request $request)
    {
        // Evita erro no DataTable enquanto a migration ainda não foi executada.
        if (! Schema::hasTable('scheduled_task_dispatches')) {
            return DataTables::of(collect())->make(true);
        }

        // Extrai os filtros e parâmetros enviados pela tabela.
        $data = $request->all();

        // Monta a consulta base com os relacionamentos necessários.
        $query = $this->loadTables();

        // Aplica filtros específicos da tela.
        $query = $this->filters($query, $data);

        // Aplica a busca textual digitada no topo.
        $query = $this->search($query, $data);

        // Ordena conforme a coluna clicada no DataTable.
        $query = $this->ordering($query, $data);

        // Formata a saída final no padrão esperado pelo DataTable.
        return $this->formatResults($query);
    }

    /**
     * Cria a consulta base do histórico de tarefas.
     * Junta o usuário responsável quando o lote foi manual.
     */
    public function loadTables()
    {
        return DB::table('scheduled_task_dispatches')
            ->leftJoin('users', 'users.id', '=', 'scheduled_task_dispatches.dispatched_by');
    }

    /**
     * Filtra os registros por origem e por resultado consolidado.
     * Isso mantém a tabela útil para auditoria e diagnóstico rápido.
     */
    public function filters($query, $data)
    {
        // Filtra entre lotes manuais e lotes do scheduler.
        if (!empty($data['source_filter']) && $data['source_filter'] !== 'all') {
            $query->where('scheduled_task_dispatches.source', $data['source_filter']);
        }

        // Filtra o resumo do lote com base no total de sucessos e falhas.
        if (!empty($data['result_filter']) && $data['result_filter'] !== 'all') {
            if ($data['result_filter'] === 'success') {
                $query->where('scheduled_task_dispatches.failure_count', 0);
            }

            if ($data['result_filter'] === 'failure') {
                $query->where('scheduled_task_dispatches.success_count', 0)
                    ->where('scheduled_task_dispatches.failure_count', '>', 0);
            }

            if ($data['result_filter'] === 'mixed') {
                $query->where('scheduled_task_dispatches.success_count', '>', 0)
                    ->where('scheduled_task_dispatches.failure_count', '>', 0);
            }
        }

        return $query;
    }

    /**
     * Aplica a busca textual sobre lote, job e usuário responsável.
     * O objetivo é localizar rapidamente uma execução específica.
     */
    public function search($query, $data)
    {
        // Reaproveita o texto digitado no campo de busca global.
        $searchBy = $data['searchBy'] ?? ($data['search']['value'] ?? null);

        if (!empty($searchBy)) {
            $query->where(function ($sub) use ($searchBy) {
                $sub->where('scheduled_task_dispatches.id', 'like', "%{$searchBy}%")
                    ->orWhere('scheduled_task_dispatches.job_name', 'like', "%{$searchBy}%")
                    ->orWhere('users.name', 'like', "%{$searchBy}%");
            });
        }

        return $query;
    }

    /**
     * Ordena a listagem conforme a coluna escolhida pelo usuário.
     * Quando não houver ordenação explícita, usa o lote mais recente primeiro.
     */
    public function ordering($query, $data)
    {
        if (!empty($data['order'])) {
            // Extrai a direção e a coluna enviadas pelo DataTable.
            $direction = $data['order'][0]['dir'];
            $index = $data['order'][0]['column'] ?? 0;
            $orderThis = $data['order_by'] ?? ($data['columns'][$index]['data'] ?? 'id');

            // Mapeia o alias da coluna da tela para a coluna real do banco.
            $column = match ($orderThis) {
                'id' => 'scheduled_task_dispatches.id',
                'job_name' => 'scheduled_task_dispatches.job_name',
                'source' => 'scheduled_task_dispatches.source',
                'started_at' => 'scheduled_task_dispatches.started_at',
                'finished_at' => 'scheduled_task_dispatches.finished_at',
                'total_clients' => 'scheduled_task_dispatches.total_clients',
                'success_count' => 'scheduled_task_dispatches.success_count',
                'failure_count' => 'scheduled_task_dispatches.failure_count',
                default => 'scheduled_task_dispatches.id',
            };

            return $query->orderBy($column, $direction);
        }

        return $query->orderByDesc('scheduled_task_dispatches.id');
    }

    /**
     * Formata os registros para o padrão visual do DataTable.
     * Converte status técnicos em rótulos amigáveis para a interface.
     */
    public function formatResults($query)
    {
        // Seleciona apenas os campos usados na listagem.
        $query->select(
            'scheduled_task_dispatches.id',
            'scheduled_task_dispatches.job_name',
            'scheduled_task_dispatches.source',
            'scheduled_task_dispatches.started_at',
            'scheduled_task_dispatches.finished_at',
            'scheduled_task_dispatches.total_clients',
            'scheduled_task_dispatches.success_count',
            'scheduled_task_dispatches.failure_count',
            'users.name as user_name'
        );

        return DataTables::query($query)
            ->addColumn('job_label', function ($row) {
                // Exibe o identificador técnico como badge amigável.
                return '<span class="badge badge-light-primary">' . e($this->formatJobLabel($row->job_name)) . '</span>';
            })
            ->addColumn('source_badge', function ($row) {
                // Diferencia visualmente lotes manuais e automáticos.
                if ($row->source === 'manual') {
                    $html = '<span class="badge badge-light-info">Manual</span>';
                    if (!empty($row->user_name)) {
                        $html .= '<div class="text-gray-500 fs-8 mt-1">por ' . e($row->user_name) . '</div>';
                    }

                    return $html;
                }

                return '<span class="badge badge-light-success">Agendado</span>';
            })
            ->editColumn('started_at', function ($row) {
                // Formata a data de início no padrão usado no painel.
                return $row->started_at ? date('d/m/Y H:i:s', strtotime($row->started_at)) : '-';
            })
            ->editColumn('finished_at', function ($row) {
                // Formata a data final para leitura rápida.
                return $row->finished_at ? date('d/m/Y H:i:s', strtotime($row->finished_at)) : '-';
            })
            ->addColumn('actions', function ($row) {
                // Direciona para a tela de detalhes do lote selecionado.
                return '<a href="' . route('task.history.show', $row->id) . '" class="btn btn-sm btn-light-primary">Visualizar</a>';
            })
            ->rawColumns(['job_label', 'source_badge', 'actions'])
            ->make(true);
    }

    /**
     * Converte o nome técnico do job em um texto amigável para a tela.
     * Isso evita expor identificadores internos diretamente ao usuário.
     */
    private function formatJobLabel($jobName)
    {
        // Traduz o lote manual para um texto mais claro.
        if ($jobName === 'manual_batch') {
            return 'Lote manual de tarefas';
        }

        // Converte nomes com underline para uma leitura mais natural.
        return ucwords(str_replace('_', ' ', (string) $jobName));
    }
}
