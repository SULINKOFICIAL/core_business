<?php

namespace App\Http\Controllers;

use App\Models\LogsApi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogsApiController extends Controller
{
    /**
     * Exibe a página de logs da API com os dados iniciais do gráfico mensal.
     */
    public function index(): View
    {
        $dailyChartData = $this->buildChartData();

        return view('pages.logs.apis.index', [
            'dailyDispatchChartLabels'     => $dailyChartData['labels'],
            'dailyDispatchChartSeries'     => $dailyChartData['series'],
            'dailyDispatchChartMonthLabel' => $dailyChartData['monthLabel'],
            'dailyDispatchChartMonthValue' => $dailyChartData['monthValue'],
        ]);
    }

    /**
     * Retorna via JSON os dados do gráfico e dos totais para o mês selecionado.
     */
    public function relatoryGraphic(Request $request): JsonResponse
    {
        // Valida o mês informado
        $validatedData = $request->validate([
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        // Obtém o mês selecionado ou usa o mês atual
        $monthValue = $validatedData['month'] ?? null;
        
        // Gera os dados do gráfico
        $dailyChartData = $this->buildChartData($monthValue);

        return response()->json($dailyChartData);
    }

    /**
     * Exibe o JSON bruto de um log específico para visualização em modal.
     */
    public function show(int $id): JsonResponse
    {
        // Busca o log e evita erro caso ele não exista.
        $logApi = LogsApi::query()->findOrFail($id);

        return response()->json($logApi->json);
    }

    /**
     * Gera dados diários e totais mensais de webhooks recebidos e despachados.
     */
    private function buildChartData(?string $monthValue = null): array
    {
        $parsedMonth = $monthValue
            ? Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth()
            : now()->startOfMonth();

        $monthStartDate = $parsedMonth->copy()->startOfMonth();
        $monthEndDate = $parsedMonth->copy()->endOfMonth();
        $daysInMonth = $monthStartDate->daysInMonth;

        // Conta despachos por dia para alimentar a série do gráfico.
        $dispatchesByDay = LogsApi::query()
            ->whereNotNull('dispatched_at')
            ->whereBetween('dispatched_at', [$monthStartDate, $monthEndDate])
            ->get(['dispatched_at'])
            ->groupBy(fn (LogsApi $logApi) => Carbon::parse($logApi->dispatched_at)->day)
            ->map(fn ($items) => $items->count());

        $labels = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => str_pad((string) $day, 2, '0', STR_PAD_LEFT))
            ->values()
            ->all();

        $dispatchesSeries = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => (int) ($dispatchesByDay[$day] ?? 0))
            ->values()
            ->all();

        // Conta recebimentos por dia para detalhar o tooltip do gráfico.
        $receivedByDay = LogsApi::query()
            ->whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->get(['created_at'])
            ->groupBy(fn (LogsApi $logApi) => Carbon::parse($logApi->created_at)->day)
            ->map(fn ($items) => $items->count());

        // Conta status por dia com base na data de criação do webhook.
        $statusByDay = LogsApi::query()
            ->whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->get(['created_at', 'status'])
            ->groupBy(fn (LogsApi $logApi) => Carbon::parse($logApi->created_at)->day)
            ->map(function ($logs) {
                return $logs->groupBy('status')
                    ->map(fn ($items) => $items->count())
                    ->map(fn (int $total) => $total)
                    ->toArray();
            });

        // Monta as séries de status por dia para habilitar a legenda clicável.
        $processedSeries = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => (int) (($statusByDay[$day]['Processado'] ?? 0)))
            ->values()
            ->all();

        $errorSeries = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => (int) (($statusByDay[$day]['Erro'] ?? 0)))
            ->values()
            ->all();

        // Define as séries exibidas no gráfico.
        $series = [
            [
                'name' => 'Recebidos',
                'data' => collect(range(1, $daysInMonth))
                    ->map(fn (int $day) => (int) ($receivedByDay[$day] ?? 0))
                    ->values()
                    ->all(),
            ],
            [
                'name' => 'Despachados',
                'data' => $dispatchesSeries,
            ],
            [
                'name' => 'Processado',
                'data' => $processedSeries,
            ],
            [
                'name' => 'Erro',
                'data' => $errorSeries,
            ],
        ];

        // Total de webhooks recebidos no mês com base no created_at.
        $receivedCount = LogsApi::query()
            ->whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->count();

        // Total de webhooks despachados no mês com base no dispatched_at.
        $dispatchedCount = LogsApi::query()
            ->whereNotNull('dispatched_at')
            ->whereBetween('dispatched_at', [$monthStartDate, $monthEndDate])
            ->count();

        // Totais por status dos webhooks recebidos no mês selecionado.
        $statusCounts = LogsApi::query()
            ->whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->toArray();

        return [
            'labels' => $labels,
            'series' => $series,
            'monthLabel' => $monthStartDate->translatedFormat('F/Y'),
            'monthValue' => $monthStartDate->format('Y-m'),
            'receivedCount' => $receivedCount,
            'dispatchedCount' => $dispatchedCount,
            'statusCounts' => $statusCounts,
        ];
    }
}
