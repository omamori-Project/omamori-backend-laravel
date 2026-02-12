<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * 성공 응답 반환
     *
     * @param mixed  $data    응답 데이터 (Resource, 배열, null)
     * @param string $message 응답 메시지
     * @param int    $status  HTTP 상태 코드
     * @return JsonResponse
     */
    protected function success(mixed $data = null, string $message = '성공', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * 페이지네이션 응답 반환
     *
     * 공통 포맷:
     * {
     *   "success": true,
     *   "message": "...",
     *   "data": [...],
     *   "meta": { "current_page", "last_page", "per_page", "total" }
     * }
     *
     * @param LengthAwarePaginator $paginator  Eloquent 페이지네이터
     * @param string               $resourceClass  JsonResource FQCN (예: OmamoriResource::class)
     * @param string               $message    응답 메시지
     * @return JsonResponse
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $message = '성공',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $resourceClass::collection($paginator->items()),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * 실패 응답 반환
     *
     * @param string $message 에러 메시지
     * @param int    $status  HTTP 상태 코드
     * @param mixed  $errors  상세 에러 (필드별 에러 등)
     * @return JsonResponse
     */
    protected function error(string $message = '실패', int $status = 400, mixed $errors = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * 201 Created 응답 반환
     *
     * @param mixed  $data    생성된 리소스
     * @param string $message 응답 메시지
     * @return JsonResponse
     */
    protected function created(mixed $data = null, string $message = '생성 완료'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * 204 No Content 응답 반환
     * - body 없이 빈 응답
     *
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}