<?php

use App\Http\Controllers\Api\V1\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->group(function (): void {
        Route::apiResource('favorites', FavoriteController::class)
            ->only(['index', 'store', 'destroy']);
    });
