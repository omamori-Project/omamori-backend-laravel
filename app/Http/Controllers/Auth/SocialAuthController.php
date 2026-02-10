<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialLinkRequest;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Google OAuth 리다이렉트 URL 반환
     * GET /api/v1/auth/google
     *
     * @return JsonResponse
     */
    public function redirect(): JsonResponse
    {
        $url = $this->socialAuthService->getRedirectUrl('google');

        return $this->success(['redirect_url' => $url]);
    }

    /**
     * Google OAuth 콜백 처리
     * GET /api/v1/auth/google/callback
     *
     * @return JsonResponse
     */
    public function callback(): JsonResponse
    {
        $result = $this->socialAuthService->handleCallback('google');

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], '소셜 로그인 성공');
    }

    /**
     * 기존 계정에 Google 연결
     * POST /api/v1/auth/google/link
     *
     * @param SocialLinkRequest $request
     * @return JsonResponse
     */
    public function link(SocialLinkRequest $request): JsonResponse
    {
        $this->socialAuthService->link(
            $request->user(),
            'google',
            $request->validated('access_token')
        );

        return $this->success(message: 'Google 계정 연결 완료');
    }

    /**
     * Google 연결 해제
     * DELETE /api/v1/auth/google/unlink
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function disconnect(Request $request): JsonResponse
    {
        $this->socialAuthService->disconnect($request->user(), 'google');

        return $this->success(message: 'Google 계정 연결 해제 완료');
    }
}