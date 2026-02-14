<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $startDate = now()->startOfMonth()->subMonths(11);

        $grouped = Client::query()
            ->where('status', true)
            ->where('created_at', '>=', $startDate)
            ->get(['created_at'])
            ->groupBy(fn (Client $client) => Carbon::parse($client->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        $monthlyActiveSystems = collect(range(0, 11))
            ->map(function (int $offset) use ($startDate, $grouped) {
                $date = (clone $startDate)->addMonths($offset);
                $key = $date->format('Y-m');

                return [
                    'month' => Carbon::parse($date)->translatedFormat('m/Y'),
                    'value' => (int) ($grouped[$key] ?? 0),
                ];
            });

        $totalActiveSystems = Client::query()
            ->where('status', true)
            ->count();

        $activeSystemsThisMonth = Client::query()
            ->where('status', true)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('pages.dashboard.index', [
            'monthlyActiveSystems' => $monthlyActiveSystems,
            'totalActiveSystems' => $totalActiveSystems,
            'activeSystemsThisMonth' => $activeSystemsThisMonth,
            'maxMonthlyValue' => max(1, $monthlyActiveSystems->max('value')),
        ]);
    }
}
