<?php

use App\Jobs\ScheduleDispatcher;
use Illuminate\Support\Facades\Schedule;

/**
 * Jobs agendados para serem executados em todos os servidores
 */
Schedule::job(new ScheduleDispatcher('finish_calls_24h'))
        ->dailyAt('00:30')
        ->onOneServer();
        
Schedule::job(new ScheduleDispatcher('finish_order_access'))
        ->dailyAt('00:15')
        ->onOneServer();
        
Schedule::job(new ScheduleDispatcher('update_s3_metrics'))
        ->dailyAt('03:00')
        ->onOneServer();
        
Schedule::job(new ScheduleDispatcher('archive_finished_tasks'))
        ->dailyAt('00:10')
        ->onOneServer();
        
Schedule::job(new ScheduleDispatcher('refresh_mercado_livre'))
        ->everySixHours()
        ->onOneServer();