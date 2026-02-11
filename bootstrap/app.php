<?php

use App\Exceptions\BusinessException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── JSON 응답만 처리 (API 요청이 아니면 기본 동작) ──

        /**
         * 인증 실패 (401)
         * - Sanctum 미인증 시 자동 발생
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '인증이 필요합니다.',
            ], 401);
        });

        /**
         * 인가 실패 (403)
         * - Policy 또는 $this->authorize() 실패 시
         */
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: '권한이 없습니다.',
            ], 403);
        });

        /**
         * 모델 Not Found (404)
         * - Route Model Binding 실패 시
         * - findOrFail(), firstOrFail() 실패 시
         */
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $model = class_basename($e->getModel());

            return response()->json([
                'success' => false,
                'message' => "{$model}을(를) 찾을 수 없습니다.",
            ], 404);
        });

        /**
         * 라우트 Not Found (404)
         * - 존재하지 않는 URL 접근 시
         */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '요청한 리소스를 찾을 수 없습니다.',
            ], 404);
        });

        /**
         * HTTP 메서드 불일치 (405)
         * - GET 라우트에 POST로 접근 등
         */
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '허용되지 않는 HTTP 메서드입니다.',
            ], 405);
        });

        /**
         * 유효성 검사 실패 (422)
         * - FormRequest 또는 ValidationException::withMessages()
         */
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '입력값 검증에 실패했습니다.',
                'errors'  => $e->errors(),
            ], 422);
        });

        /**
         * Rate Limit 초과 (429)
         */
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.',
            ], 429);
        });

        /**
         * 비즈니스 로직 예외 (커스텀 상태 코드)
         * - Service 계층에서 throw new BusinessException(...)
         */
        $exceptions->render(function (BusinessException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatus());
        });

        /**
         * Symfony HttpException (다양한 상태 코드)
         */
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: '요청을 처리할 수 없습니다.',
            ], $e->getStatusCode());
        });

    })->create();