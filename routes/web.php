<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\AnalyticsController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ItemController::class, 'index']);
Route::get('/{id}', [ItemController::class, 'show']);

Route::get('/analytics', [AnalyticsController::class, 'index']);