<?php

namespace App\Http\Controllers;

use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApisNewsController extends Controller
{
    
    /**
     * Obtém lista de notícias do sistema.
     */
    public function index(Request $request)
    {

        // Pega offset e limit da request (com valores padrão)
        $offset = (int) $request->input('offset', 0);
        $limit  = (int) $request->input('limit', 10);

        // Limita os valores máximos para segurança
        $limit  = max(1, min($limit, 20));
        $offset = max(0, $offset);

        // Obtém notícias
        $news = News::orderBy('created_at', 'desc')
                            ->where('status', true)
                            ->with('category')
                            ->skip($offset)
                            ->take($limit)
                            ->get();

        // Retorne também o total, para facilitar paginação no front
        $total = News::count();

        // Formata as notícias
        $news = $news->map(function ($item) {
            return $this->formatNews($item);
        });
        
        // Obtém última atualização
        $lastUpdate = News::max('updated_at');

        // Retorna dados
        return response()->json([
            'offset'        => $offset,
            'limit'         => $limit,
            'total'         => $total,
            'last_update'   => $lastUpdate,
            'data'          => $news,
        ], 200);

    }

    /**
     * Obtém detalhes de uma notícia.
     */
    public function show($id)
    {

        // Obtém notícia
        $news = News::findOrFail($id);
        
        // Retorna dados
        return response()->json($this->formatNews($news), 200);

    }
    
    /**
     * Obtém notícias não lidas.
     */
    public function notRead($id)
    {

        // Obtém hoje
        $today = Carbon::today();

        // Lista de IDs que o usuário já viu (exemplo: array ou tabela no banco)
        $seenNewsIds = [];

        // Busca as novidades ativas para hoje e que não foram vistas
        $news = News::where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    // ->whereNotIn('id', $seenNewsIds)
                    ->get();

        // Formata as notícias
        $news = $news->map(function ($item) {
            return $this->formatNews($item);
        });

        // Retorna IDS
        return response()->json($news, 200);

    }
    
    /**
     * Marca notícias como lida.
     */
    public function markRead(Request $request, $id)
    {

        // Obtém dados
        $data = $request->all();

        dd($data);

        // Retorna IDS
        return response()->json($news, 200);

    }

    /**
     * Formata notícia
     */
    private function formatNews($news)
    {
        return [
            'id'        => $news->id,
            'title'     => $news->title,
            'resume'    => $news->resume,
            'body'      => $news->body,
            'tags'      => $news->tags,
            'category'  => [
                'name'  => $news->category->name ?? null,
                'color' => $news->category->color ?? null,
            ],
            'priority'   => $news->priority,
            'start_date' => optional($news->start_date)->format('d/m/Y'),
            'end_date'   => optional($news->end_date)->format('d/m/Y'),
            'cta_text'   => $news->cta_text,
            'cta_url'    => $news->cta_url,
            'created_at' => $news->created_at->format('d/m/Y H:i:s'),
        ];
    }


}
