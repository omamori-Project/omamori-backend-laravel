<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * 비즈니스 로직 예외
 *
 * Service 계층에서 비즈니스 규칙 위반 시 사용
 * - 기존 \Exception 대신 명시적인 HTTP 상태 코드를 지정할 수 있도록 확장
 * - bootstrap/app.php의 글로벌 예외 핸들러에서 JSON 응답으로 변환하여 API 클라이언트에 전달
 */
class BusinessException extends RuntimeException
{
    /**
     * @param string $message 에러 메시지
     * @param int    $status  HTTP 상태 코드 (기본 422)
     */
    public function __construct(
        string $message = '',
        private readonly int $status = 422
    ) {
        parent::__construct($message);
    }

    /**
     * HTTP 상태 코드 반환
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}