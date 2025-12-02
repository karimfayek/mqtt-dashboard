<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatsController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/readings/latest', [StatsController::class, 'latest']);
Route::get('/api/readings/summary', [StatsController::class, 'toiletsSummary']);
Route::get('/api/usage/per-toilet', [StatsController::class, 'usagePerToilet']);
Route::get('/api/usage/hourly', [StatsController::class, 'hourlyUsage']);
