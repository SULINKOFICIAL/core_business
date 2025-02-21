<?php

use App\Http\Controllers\ApisController;
use Illuminate\Support\Facades\Route;

Route::get('/get-database', [ApisController::class, 'getDatabase']);
Route::post('/error', [ApisController::class, 'notifyErrors']);
Route::post('/tickets', [ApisController::class, 'tickets']);