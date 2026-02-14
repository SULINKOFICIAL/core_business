<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NewsProcessingController extends Controller
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
        return News::with('category');
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
                $sub->where('title', 'like', "%{$searchBy}%")
                    ->orWhere('cta_text', 'like', "%{$searchBy}%");
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
                'title' => 'title',
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
            ->editColumn('title', function ($news) {
                $html = '<a href="' . route('news.show', $news->id) . '" class="text-gray-700 text-hover-primary fw-bold">' . e($news->title) . '</a>';
                if (!empty($news->cta_url) && !empty($news->cta_text)) {
                    $html .= ' <a href="' . e($news->cta_url) . '" class="badge badge-success bg-hover-primary" target="_blank">' . e($news->cta_text) . '</a>';
                }
                return $html;
            })
            ->addColumn('period', function ($news) {
                return '<p class="text-gray-700 fw-bold mb-0">' . $news->start_date?->format('d/m/Y') . ' até ' . $news->end_date?->format('d/m/Y') . '</p>';
            })
            ->addColumn('category_label', function ($news) {
                if (!$news->category) {
                    return '<span class="badge badge-light-secondary">Sem categoria</span>';
                }
                return '<span class="badge badge-light-success" style="background: ' . hex2rgb($news->category->color, 15) . '; color: ' . e($news->category->color) . '">' . e($news->category->name) . '</span>';
            })
            ->addColumn('priority_label', function ($news) {
                return match ($news->priority) {
                    'high' => '<span class="badge badge-light-success">Alta</span>',
                    'medium' => '<span class="badge badge-light-warning">Média</span>',
                    default => '<span class="badge badge-light-danger">Baixa</span>',
                };
            })
            ->addColumn('status_label', function ($news) {
                if ((int) $news->status === 0) {
                    return '<span class="badge badge-light-danger">Desabilitado</span>';
                }
                return '<span class="badge badge-light-success">Habilitado</span>';
            })
            ->addColumn('actions', function ($news) {
                return '<div class="d-flex gap-4 align-items-center justify-content-center">'
                    . '<a href="' . route('news.edit', $news->id) . '" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">Editar</a>'
                    . '<a href="' . route('news.show', $news->id) . '" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">Visualizar</a>'
                    . '</div>';
            })
            ->rawColumns(['title', 'period', 'category_label', 'priority_label', 'status_label', 'actions'])
            ->make(true);
    }
}
