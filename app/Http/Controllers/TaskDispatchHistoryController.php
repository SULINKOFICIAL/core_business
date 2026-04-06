<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTaskDispatch;
use App\Models\ScheduledTaskDispatchItem;
use Illuminate\Http\Request;

class TaskDispatchHistoryController extends Controller
{
    protected $request;
    private $repository;
    private $repositoryItems;

    /**
     * Inicializa os modelos usados na tela de histórico de tarefas.
     * Mantém o controller alinhado ao padrão usado nos outros módulos.
     */
    public function __construct(Request $request, ScheduledTaskDispatch $content, ScheduledTaskDispatchItem $items)
    {
        $this->request = $request;
        $this->repository = $content;
        $this->repositoryItems = $items;
    }

    /**
     * Exibe a listagem principal do histórico de tarefas.
     * Os registros são carregados via DataTable na rota de processamento.
     */
    public function index()
    {
        // Retorna a página base para o DataTable montar a listagem.
        return view('pages.task_history.index');
    }

    /**
     * Exibe os detalhes de uma execução com os clientes disparados.
     * A tela mostra status, mensagem e retorno da API por item.
     */
    public function show($id)
    {
        // Busca a execução principal com o usuário que disparou o lote.
        $dispatch = $this->repository->query()
            ->with(['user'])
            ->findOrFail($id);

        // Carrega os itens paginados para manter a tela leve.
        $items = $this->repositoryItems->query()
            ->with(['tenant'])
            ->where('dispatch_id', $dispatch->id)
            ->orderBy('id')
            ->paginate(50);

        // Entrega a execução e seus itens para a view de detalhes.
        return view('pages.task_history.show', [
            'dispatch' => $dispatch,
            'items' => $items,
        ]);
    }
}
