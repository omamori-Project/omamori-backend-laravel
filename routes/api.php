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
use App\Http\Controllers\Community\CommentController;
use App\Http\Controllers\Community\PostLikeController;
use App\Http\Controllers\Community\PostBookmarkController;
use App\Http\Controllers\Community\MeBookmarkController;

use App\Http\Controllers\Community\PostController;

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
     * Community (Public)
     *
     * - GET /api/v1/posts        : 피드 목록(공개)
     * - GET /api/v1/posts/{postId} : 게시글 상세(공개)
     * - GET /api/v1/posts/{postId}/comments : 게시글 댓글 목록(공개)
     */
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']); 
        Route::get('/{postId}', [PostController::class, 'show']); 
        Route::get('/{postId}/comments', [CommentController::class, 'index']);
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
         * - POST   /api/v1/omamoris                     : 오마모리 생성
         * - GET    /api/v1/omamoris                     : 오마모리 목록
         * - GET    /api/v1/omamoris/{omamori}              : 오마모리 상세
         * - PATCH  /api/v1/omamoris/{omamori}              : 오마모리 수정
         * - PATCH  /api/v1/omamoris/{omamori}/back-message   : 오마모리 뒷면 메시지 수정
         * - DELETE /api/v1/omamoris/{omamori}              : 오마모리 삭제
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
             *  - POST /api/v1/omamoris/{omamori}/share   : 오마모리 공유 생성
             *  - GET  /api/v1/omamoris/{omamori}/shares  : 오마모리 공유 목록
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
         *  - PATCH  /api/v1/shares/{shareId}   : 공유 설정 수정
         *  - DELETE /api/v1/shares/{shareId}   : 공유 삭제
         */
        Route::patch('/shares/{shareId}', [ShareController::class, 'update']);
        Route::delete('/shares/{shareId}', [ShareController::class, 'destroy']);

        /**
         * Community (Private)
         *
         * - POST   /api/v1/posts                    : 게시글 작성
         * - PATCH  /api/v1/posts/{postId}           : 게시글 수정 (owner 또는 admin)
         * - DELETE /api/v1/posts/{postId}           : 게시글 삭제 (owner 또는 admin, soft delete)
         * - GET    /api/v1/me/posts                 : 내 게시글 목록
         * - GET    /api/v1/users/{userId}/posts     : 특정 유저 게시글 목록(로그인 필요 버전)
         * - POST   /api/v1/posts/{postId}/comments  : 게시글 댓글 작성
         */
        Route::prefix('posts')->group(function () {
            Route::post('/', [PostController::class, 'store']);
            Route::patch('/{postId}', [PostController::class, 'update']);
            Route::delete('/{postId}', [PostController::class, 'destroy']);
            Route::post('/{postId}/comments', [CommentController::class, 'store']);
        });
        
        Route::get('/me/posts', [PostController::class, 'myIndex']);
        Route::get('/users/{userId}/posts', [PostController::class, 'userIndex']);
        
        /**
         * Community (Private) - Comments
         *
         * - POST   /api/v1/comments/{commentId}/replies : 답글 작성
         * - PATCH  /api/v1/comments/{commentId}         : 댓글/답글 수정 (owner 또는 admin)
         * - DELETE /api/v1/comments/{commentId}         : 댓글/답글 삭제 (owner 또는 admin)
         */
        Route::prefix('comments')->group(function () {
            Route::post('/{commentId}/replies', [CommentController::class, 'storeReply']);
            Route::patch('/{commentId}', [CommentController::class, 'update']);
            Route::delete('/{commentId}', [CommentController::class, 'destroy']);
        });
        
        /**
         * Community (Private) - Me
         *
         * - GET /api/v1/me/comments : 내 댓글/답글 목록
         */
        Route::prefix('me')->group(function () {
            Route::get('/comments', [CommentController::class, 'myIndex']);
        });

        /**
         * Community (Private) - likes
         * - POST   /api/v1/posts/{postId}/likes : 게시글 좋아요 추가
         * - DELETE /api/v1/posts/{postId}/likes : 게시글 좋아요 제거
         * - GET    /api/v1/posts/{postId}/likes/me : 좋아요 목록
         */
        Route::post('/posts/{post}/likes', [PostLikeController::class, 'store']);
        Route::delete('/posts/{post}/likes', [PostLikeController::class, 'destroy']);
        Route::get('/posts/{post}/likes/me', [PostLikeController::class, 'me']);

        /**
         * Community (Private) - bookmarks
         * - POST   /api/v1/posts/{postId}/bookmarks : 게시글 북마크 추가
         * - DELETE /api/v1/posts/{postId}/bookmarks : 게시글 북마크 제거
         * - GET    /api/v1/me/bookmarks            : 내 북마크 목록
         */
        Route::post('/posts/{post}/bookmarks', [PostBookmarkController::class, 'store']);
        Route::delete('/posts/{post}/bookmarks', [PostBookmarkController::class, 'destroy']);
        Route::get('/me/bookmarks', [MeBookmarkController::class, 'index']);        
    });
});