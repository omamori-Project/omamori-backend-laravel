<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Community\PostBookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostBookmarkController extends Controller
{
    public function __construct(
        private readonly PostBookmarkService $postBookmarkService,
    ) {
    }

    /**
     * 게시글 북마크 추가
     *
     * POST /api/v1/posts/{post}/bookmarks
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->postBookmarkService->bookmark($post, $user);

        return $this->noContent();
    }

    /**
     * 게시글 북마크 취소
     *
     * DELETE /api/v1/posts/{post}/bookmarks
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->postBookmarkService->unbookmark($post, $user);

        return $this->noContent();
    }
}