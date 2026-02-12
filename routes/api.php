<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\SocialAuthController;

use App\Http\Controllers\Omamori\OmamoriController;
use App\Http\Controllers\Omamori\OmamoriElementController;
use App\Http\Controllers\Omamori\OmamoriShareController;
use App\Http\Controllers\Omamori\ShareController;
use App\Http\Controllers\Public\PublicShareController;
use App\Http\Controllers\Omamori\OmamoriExportController;
use App\Http\Controllers\Omamori\OmamoriDuplicateController;

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

    Route::prefix('public')->group(function () {
        Route::get('/shares/{token}', [PublicShareController::class, 'show']);
        Route::get('/shares/{token}/preview', [PublicShareController::class, 'preview']);        
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

            // 기본 CRUD
            Route::post('/', [OmamoriController::class, 'store']);
            Route::get('/', [OmamoriController::class, 'index']);
            Route::get('/{omamori}', [OmamoriController::class, 'show']);
            Route::patch('/{omamori}', [OmamoriController::class, 'update']);
            Route::patch('/{omamori}/back-message', [OmamoriController::class, 'updateBackMessage']);
            Route::delete('/{omamori}', [OmamoriController::class, 'destroy']);

            // Elements (부모-자식 바인딩 강제)
            Route::scopeBindings()->group(function () {
                Route::post('/{omamori}/elements', [OmamoriElementController::class, 'store']);
                Route::patch('/{omamori}/elements/{element}', [OmamoriElementController::class, 'update']);
                Route::delete('/{omamori}/elements/{element}', [OmamoriElementController::class, 'destroy']);
                Route::post('/{omamori}/elements/reorder', [OmamoriElementController::class, 'reorder']);
            });

            Route::post('/{omamori}/save-draft', [OmamoriController::class, 'saveDraft']);
            Route::post('/{omamori}/publish', [OmamoriController::class, 'publish']);

            /**
             *Share (오마모리 기준)
             *  - POST /api/v1/omamoris/{omamori}/share
             *  - GET  /api/v1/omamoris/{omamori}/shares
             */
            Route::post('/{omamori}/share', [OmamoriShareController::class, 'store']);
            Route::get('/{omamori}/shares', [OmamoriShareController::class, 'index']);

            // 오마모리 내보내기 (다운로드 URL 반환)
            Route::post('/{omamoriId}/export', [OmamoriExportController::class, 'export']);

            // 오마모리 복제
            Route::post('/{omamoriId}/duplicate', [OmamoriDuplicateController::class, 'duplicate']);            
        });

        /**
         * Share (공유 기준)
         *  - PATCH  /api/v1/shares/{shareId}
         *  - DELETE /api/v1/shares/{shareId}
         */
        Route::patch('/shares/{shareId}', [ShareController::class, 'update']);
        Route::delete('/shares/{shareId}', [ShareController::class, 'destroy']);
    });
});