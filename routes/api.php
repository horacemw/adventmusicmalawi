<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => ['ok' => true, 'app' => config('app.name'), 'time' => now()->toIso8601String()]);

    Route::get('/user', fn (Request $request) => $request->user())
        ->middleware('auth:sanctum');

    // Public discovery endpoints (mounted here so the future mobile app hits the same handlers)
    // Actual controllers will be filled in during the API phase.
});
