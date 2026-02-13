<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Post\IndexMyBookmarksRequest;
use App\Http\Resources\Community\Post\BookmarkedPostResource;
use App\Services\Community\PostBookmarkService;
use Illuminate\Http\JsonResponse;

class MeBookmarkController extends Controller
{
    public function __construct(
        private readonly PostBookmarkService $postBookmarkService,
    ) {
    }

    /**
     * 내 북마크 게시글 목록 조회
     *
     * GET /api/v1/me/bookmarks?page=&size=
     *
     * @param IndexMyBookmarksRequest $request
     * @return JsonResponse
     */
    public function index(IndexMyBookmarksRequest $request): JsonResponse
    {
        $user = $request->user();

        $perPage = $request->perPage(10);

        $paginator = $this->postBookmarkService->myBookmarks($user, $perPage);

        return $this->paginated($paginator, BookmarkedPostResource::class);
    }
}