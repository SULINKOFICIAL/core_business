<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Ticket $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }

    /**
     * Retorna a pagina principal de tickets da central.
     */
    public function index()
    {
        $contents = $this->repository->all();

        return view('pages.tickets.index')->with([
            'contents' => $contents,
        ]);
    }

    /**
     * Abre o ticket no modal da central e assume o atendimento quando necessario.
     */
    public function show($id)
    {
        // Precarrega tudo que o modal da central precisa renderizar.
        $ticket = $this->repository->with(['client', 'replies.user', 'replies.client', 'attachments', 'filedByUser', 'finishedByUser'])->findOrFail($id);

        // Ao abrir o ticket, ele deixa de ser pendente e passa para em andamento.
        if ($ticket->progress !== 'fechado')
        {
            $ticket->update([
                'progress' => 'em andamento',
                'filed_by' => Auth::id(),
                'opened_at' => $ticket->opened_at ?? now(),
            ]);

            // Recarrega o modelo para refletir os metadados de abertura no modal.
            $ticket->refresh();
            $ticket->load(['client', 'replies.user', 'replies.client', 'attachments', 'filedByUser', 'finishedByUser']);
        }

        return view('pages.tickets._modal')->with([
            'ticket' => $ticket,
        ]);
    }

    /**
     * Registra uma resposta do atendente no historico do ticket.
     */
    public function reply(Request $request, $id)
    {
        // Registra a resposta como autoria do usuario interno autenticado.
        $ticket = $this->repository->findOrFail($id);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
        ]);

        $ticket->update([
            'progress' => 'em andamento',
        ]);

        return response()->json([
            'message' => 'Resposta enviada com sucesso'
        ]);
    }

    /**
     * Finaliza o ticket e registra quem concluiu o atendimento.
     */
    public function finish($id)
    {
        // Mantem rastro de quem finalizou e quando isso ocorreu.
        $ticket = $this->repository->findOrFail($id);

        $ticket->update([
            'progress' => 'fechado',
            'finished_by' => Auth::id(),
            'finished_at' => now(),
        ]);

        return response()->json([
            'message' => 'Ticket finalizado com sucesso'
        ]);
    }

    /**
     * Atualiza manualmente os dados do ticket quando necessario.
     */
    public function update(Request $request, $id)
    {
        $requestData = $request->all();
        $requestData['updated_by'] = Auth::id();

        // Aplica a atualizacao direta somente no registro solicitado.
        $content = $this->repository->find($id);
        $content->update($requestData);

        return response()->json([
            'message' => 'Ticket Atualizado com sucesso'
        ]);
    }
}
