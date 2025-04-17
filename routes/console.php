<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerateRenewalOrders;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

/**
 * Agenda a geração automática de faturas para clientes 
 * cujos sistemas expiram em até 5 dias.
 * 
 * Este job é executado diariamente às 04:00 da manhã 
 * e garante que não ocorra sobreposição de execuções.
 */
Schedule::job(new GenerateRenewalOrders)
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->before(function () {
        Log::info('[' . now()->toDateTimeString() . '] Vai rodar o Job de Renovação...');
    })
    ->after(function () {
        Log::info('[' . now()->toDateTimeString() . '] Terminou de rodar o Job de Renovação.');
    });
