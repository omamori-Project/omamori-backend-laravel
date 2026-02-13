<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Community\PostLikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function __construct(
        private readonly PostLikeService $postLikeService,
    ) {
    }

    /**
     * 게시글 좋아요 추가
     *
     * POST /api/v1/posts/{post}/likes
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->postLikeService->like($post, $user);

        return $this->noContent();
    }

    /**
     * 게시글 좋아요 취소
     *
     * DELETE /api/v1/posts/{post}/likes
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->postLikeService->unlike($post, $user);

        return $this->noContent();
    }

    /**
     * 내 게시글 좋아요 여부 조회
     *
     * GET /api/v1/posts/{post}/likes/me
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function me(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $liked = $this->postLikeService->isLikedByMe($post, $user);

        return $this->success([
            'liked' => $liked,
        ]);
    }
}