<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Monta os dados da dashboard e retorna a view principal.
     * Calcula métricas mensais, totais atuais e a lista dos últimos MiCores.
     */
    public function index(): View
    {
        // Define o período base dos últimos 12 meses (incluindo o mês atual).
        $startDate = now()->startOfMonth()->subMonths(11);

        // Agrupa clientes ativos por mês de criação para gerar a série mensal.
        $grouped = Client::where('status', true)
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->groupBy(fn (Client $client) => Carbon::parse($client->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        // Garante todos os meses no gráfico, preenchendo com zero quando não houver dados.
        $monthlyActiveSystems = collect(range(0, 11))
            ->map(function (int $offset) use ($startDate, $grouped) {
                $date = (clone $startDate)->addMonths($offset);
                $key = $date->format('Y-m');

                return [
                    'month' => Carbon::parse($date)->translatedFormat('m/Y'),
                    'value' => (int) ($grouped[$key] ?? 0),
                ];
            });

        // Total geral de sistemas ativos.
        $totalActiveSystems = Client::where('status', true)
            ->count();

        // Total de sistemas ativos criados no mês atual.
        $activeSystemsThisMonth = Client::where('status', true)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // Busca os 5 MiCores mais recentes para o card de resumo.
        $latestMiCores = Client::with('domains')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Define o intervalo do mês atual para montar o gráfico diário.
        $monthStartDate = now()->startOfMonth();
        $monthEndDate = now()->endOfMonth();
        $daysInMonth = $monthStartDate->daysInMonth;

        // Agrupa os sistemas criados por dia do mês atual.
        $createdByDay = Client::whereBetween('created_at', [$monthStartDate, $monthEndDate])
            ->get(['created_at'])
            ->groupBy(fn (Client $client) => Carbon::parse($client->created_at)->day)
            ->map(fn ($items) => $items->count());

        // Gera os rótulos dos dias (01..31) e os valores de cada dia.
        $dailyChartLabels = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => str_pad((string) $day, 2, '0', STR_PAD_LEFT))
            ->values();

        $dailyChartSeries = collect(range(1, $daysInMonth))
            ->map(fn (int $day) => (int) ($createdByDay[$day] ?? 0))
            ->values();

        $dailyChartMonthLabel = $monthStartDate->translatedFormat('F/Y');

        // Retorna a view com os dados consolidados da dashboard.
        return view('pages.dashboard.index', [
            'monthlyActiveSystems' => $monthlyActiveSystems,
            'totalActiveSystems' => $totalActiveSystems,
            'activeSystemsThisMonth' => $activeSystemsThisMonth,
            'maxMonthlyValue' => max(1, $monthlyActiveSystems->max('value')),
            'latestMiCores' => $latestMiCores,
            'dailyChartLabels' => $dailyChartLabels,
            'dailyChartSeries' => $dailyChartSeries,
            'dailyChartMonthLabel' => $dailyChartMonthLabel,
        ]);
    }
}
