<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
    
    /**
     * 성공 응답 반환
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function success(mixed $data = null, string $message = '성공', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * 실패 응답 반환
     * @param string $message
     * @param int $status
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function error(string $message = '실패', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * 201 Created 응답 반환
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function created(mixed $data = null, string $message = '생성 완료'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }
    
    /**
     * 204 No Content 응답 반환
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}