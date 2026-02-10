<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\IdentityResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * 내 정보 조회
     * GET /api/v1/me
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user())
        );
    }

    /**
     * 회원 정보 수정
     * PATCH /api/v1/me
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->success(
            new UserResource($user),
            '회원 정보 수정 완료'
        );
    }

    /**
     * 회원 탈퇴
     * DELETE /api/v1/me
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return $this->success(message: '회원 탈퇴 완료');
    }

    /**
     * 내 연결된 로그인 수단 목록
     * GET /api/v1/me/identities
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function identities(Request $request): JsonResponse
    {
        $identities = $this->authService->getIdentities($request->user());

        return $this->success(
            IdentityResource::collection($identities)
        );
    }
}