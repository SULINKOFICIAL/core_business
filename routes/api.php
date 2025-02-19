<?php

use App\Http\Controllers\ApisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/get-database', [ApisController::class, 'getDatabase']);
Route::post('/error', [ApisController::class, 'notifyErrors']);