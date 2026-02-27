<?php

namespace App\Http\Controllers;

use App\Jobs\ScheduleDispatcher;
use App\Models\Order;
use App\Models\OrderSubscription;
use App\Models\OrderTransaction;
use App\Services\PagarMeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeveloperController extends Controller
{
    public function test() {}

    public function runScheduledNow(Request $request): RedirectResponse
    {
        $jobs = [
            'finish_calls_24h',
            'finish_order_access',
            'update_s3_metrics',
            'archive_finished_tasks',
            'refresh_mercado_livre',
        ];

        $executed = [];
        $failed = [];

        foreach ($jobs as $jobName) {
            try {
                ScheduleDispatcher::dispatchSync($jobName);
                $executed[] = $jobName;
            } catch (Throwable $exception) {
                $failed[] = $jobName;

                Log::error('Falha ao disparar job manual no header', [
                    'job_name' => $jobName,
                    'user_id' => $request->user()?->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if (! empty($failed)) {
            $message = 'Disparo manual concluído com falhas. Jobs executados: '
                . implode(', ', $executed)
                . '. Jobs com erro: '
                . implode(', ', $failed)
                . '.';

            return back()->with('message', $message);
        }

        return back()->with('message', 'Disparo manual concluído com sucesso para: ' . implode(', ', $executed) . '.');
    }
}
