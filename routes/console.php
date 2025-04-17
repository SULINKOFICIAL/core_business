<?php

use App\Jobs\ChargeSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerateRenewalOrders;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

/**
 * Gera os pedidos de renovação dos clientes.
 */
Schedule::job(GenerateRenewalOrders::class)
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->before(function () {
        Log::info('[' . now()->toDateTimeString() . '] Vai rodar o Job de Renovação...');
    })
    ->after(function () {
        Log::info('[' . now()->toDateTimeString() . '] Terminou de rodar o Job de Renovação.');
    });

/**
 * Realiza cobrança dos pedidos dos clientes
 */
Schedule::job(ChargeSubscriptions::class)
    ->dailyAt('07:30')
    ->withoutOverlapping()
    ->before(function () {
        Log::info('[' . now()->toDateTimeString() . '] Vai rodar o Job de Cobrança...');
    })
    ->after(function () {
        Log::info('[' . now()->toDateTimeString() . '] Terminou de rodar o Job de Cobrança.');
    });