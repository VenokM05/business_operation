<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\ServiceRequestApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (PRD Section 13) — Sanctum bearer auth, shared policies, envelope.
| The /api prefix is applied automatically by the framework.
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('dashboard', DashboardApiController::class);
        Route::apiResource('clients', ClientApiController::class);
        Route::apiResource('projects', ProjectApiController::class);
        Route::apiResource('service-requests', ServiceRequestApiController::class);
    });
});
