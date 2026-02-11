<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\SocialAuthController;

use App\Http\Controllers\Omamori\OmamoriController;
use App\Http\Controllers\Omamori\OmamoriElementController;

Route::prefix('v1')->group(function () {

    /**
     * Public (비로그인)
     */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/google', [SocialAuthController::class, 'redirect']);
        Route::get('/google/callback', [SocialAuthController::class, 'callback']);
    });

    /**
     * Private (로그인 필요)
     */
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);

            Route::post('/google/link', [SocialAuthController::class, 'link']);
            Route::delete('/google/unlink', [SocialAuthController::class, 'disconnect']);
        });

        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::delete('/me', [MeController::class, 'destroy']);
        Route::get('/me/identities', [MeController::class, 'identities']);

        /**
         * Omamori
         */
        Route::prefix('omamoris')->group(function () {

            // Phase 1: 기본 CRUD
            Route::post('/', [OmamoriController::class, 'store']);
            Route::get('/', [OmamoriController::class, 'index']);
            Route::get('/{omamori}', [OmamoriController::class, 'show']);
            Route::patch('/{omamori}', [OmamoriController::class, 'update']);
            Route::patch('/{omamori}/back-message', [OmamoriController::class, 'updateBackMessage']);
            Route::delete('/{omamori}', [OmamoriController::class, 'destroy']);

            // Phase 2: Elements (부모-자식 바인딩 강제)
            Route::scopeBindings()->group(function () {
                Route::post('/{omamori}/elements', [OmamoriElementController::class, 'store']);
                Route::patch('/{omamori}/elements/{element}', [OmamoriElementController::class, 'update']);
                Route::delete('/{omamori}/elements/{element}', [OmamoriElementController::class, 'destroy']);
                Route::post('/{omamori}/elements/reorder', [OmamoriElementController::class, 'reorder']);
            });
        });
    });
});