<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class ApisTicketsController extends Controller
{
    /**
     * Cria um ticket para o cliente autenticado na API e define o estado inicial.
     */
    public function store(Request $request)
    {
        
        // Valida apenas os campos essenciais para a abertura do ticket.
        $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string'],
        ]);

        $requestData = $request->all();
        $client = $request->input('client');

        // Normaliza o usuario solicitante quando ele chega serializado em JSON.
        if (!empty($requestData['requester_user']) && is_string($requestData['requester_user']))
        {
            $requestData['requester_user'] = json_decode($requestData['requester_user'], true);
        }

        // Forca o vinculo do ticket ao cliente autenticado na API.
        $requestData['client_id'] = $client->id;
        $requestData['progress'] = 'pendente';

        $ticket = Ticket::create($requestData);

        return response()->json([
            'message' => 'Ticket criado com sucesso!',
            'id' => $ticket->id,
        ], 201);
    }

    /**
     * Retorna a listagem de tickets vinculados ao cliente autenticado.
     */
    public function index(Request $request)
    {
        $client = $request->input('client');

        // Restringe a consulta ao cliente do token para evitar vazamento entre tenants.
        return response()->json(
            Ticket::query()
                ->where('client_id', $client->id)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Retorna os detalhes de um ticket com respostas e anexos.
     */
    public function show(Request $request, $id)
    {
        $client = $request->input('client');

        // Carrega as relacoes usadas no modal do coresulink.
        $ticket = Ticket::with(['replies.user', 'replies.client'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        return response()->json($this->formatTicket($ticket));
    }

    /**
     * Registra uma nova resposta do cliente e move o ticket para em andamento.
     */
    public function reply(Request $request, $id)
    {
        $client = $request->input('client');

        // Garante que o cliente so responda em tickets da propria empresa.
        $ticket = Ticket::where('client_id', $client->id)->findOrFail($id);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'client_id' => $client->id,
            'message' => $request->input('message'),
        ]);

        // Toda nova interacao do cliente recoloca o ticket em atendimento.
        $ticket->update([
            'progress' => 'em andamento',
        ]);

        return response()->json('Resposta enviada com sucesso!', 201);
    }

    /**
     * Registra na central os anexos que ja foram enviados pelo coresulink.
     */
    public function attach(Request $request, $id)
    {
        // Recebe apenas metadados, pois o arquivo ja foi salvo no S3 pelo coresulink.
        $request->validate([
            'attachments' => ['required', 'array', 'max:5'],
            'attachments.*.original_name' => ['required', 'string', 'max:255'],
            'attachments.*.file_path' => ['required', 'string', 'max:255'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:120'],
            'attachments.*.size_bytes' => ['nullable', 'integer'],
        ]);

        $client = $request->input('client');
        $ticket = Ticket::where('client_id', $client->id)->findOrFail($id);

        foreach ($request->input('attachments', []) as $attachment)
        {
            // Persiste o caminho final para a central conseguir listar e abrir os anexos.
            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'original_name' => $attachment['original_name'],
                'file_path' => $attachment['file_path'],
                'mime_type' => $attachment['mime_type'] ?? null,
                'size_bytes' => $attachment['size_bytes'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Anexos registrados com sucesso!',
        ], 201);
    }

    /**
     * Formata o ticket para a resposta da API com estrutura estavel para o coresulink.
     */
    private function formatTicket(Ticket $ticket): array
    {
        // Garante carga tardia das relacoes caso o ticket venha sem preload.
        $ticket->loadMissing(['replies.user', 'replies.client']);
        $ticket->loadMissing('attachments');

        return [
            'id' => $ticket->id,
            'client_id' => $ticket->client_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'progress' => $ticket->progress,
            'status' => $ticket->status,
            'created_at' => optional($ticket->created_at)->toISOString(),
            'replies' => $ticket->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'message' => $reply->message,
                    'created_at' => optional($reply->created_at)->toISOString(),
                    'author_type' => $reply->user_id ? 'user' : 'client',
                    'author_name' => $reply->user?->name ?? $reply->client?->name ?? 'Cliente',
                ];
            })->values()->all(),
            'attachments' => $ticket->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->original_name,
                    'url' => $attachment->url(),
                    'size_bytes' => $attachment->size_bytes,
                ];
            })->values()->all(),
        ];
    }
}
