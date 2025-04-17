<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerateRenewalOrders;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new GenerateRenewalOrders)
    ->everyMinute()
    ->withoutOverlapping()
    ->before(function () {
        Log::info('[' . now()->toDateTimeString() . '] Vai rodar o Job de Renovação...');
    })
    ->after(function () {
        Log::info('[' . now()->toDateTimeString() . '] Terminou de rodar o Job de Renovação.');
    });
