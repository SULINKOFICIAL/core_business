<?php

use App\Jobs\ChargeSubscriptions;
use App\Jobs\GenerateRenewalOrders;
use App\Jobs\ScheduleDispatcher;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

/**
 * Gera os pedidos de renovação dos clientes.
 */
Schedule::job(GenerateRenewalOrders::class)
    ->dailyAt('03:00')
    ->withoutOverlapping();

/**
 * Realiza cobrança dos pedidos dos clientes
 */
Schedule::job(ChargeSubscriptions::class)
    ->dailyAt('07:30')
    ->withoutOverlapping();


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