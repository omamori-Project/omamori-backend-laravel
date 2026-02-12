<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Post\IndexPostRequest;
use App\Http\Requests\Community\Post\StorePostRequest;
use App\Http\Requests\Community\Post\UpdatePostRequest;
use App\Http\Resources\Community\Post\PostResource;
use App\Services\Community\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * @var PostService
     */
    protected PostService $postService;

    /**
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * 게시글 피드 목록 (공개)
     *
     * GET /api/v1/posts?page=&size=&sort=
     *
     * @param IndexPostRequest $request
     * @return JsonResponse
     */
    public function index(IndexPostRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $paginator = $this->postService->paginateFeed($filters);

        return $this->paginated($paginator, PostResource::class);
    }

    /**
     * 게시글 상세 (공개)
     *
     * GET /api/v1/posts/{postId}
     *
     * @param int $postId
     * @return JsonResponse
     */
    public function show(int $postId): JsonResponse
    {
        $post = $this->postService->show($postId, true);

        return $this->success(new PostResource($post));
    }

    /**
     * 게시글 작성 (로그인 필요)
     *
     * POST /api/v1/posts
     *
     * @param StorePostRequest $request
     * @return JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $post = $this->postService->store($user, $data);

        return $this->created(new PostResource($post));
    }

    /**
     * 게시글 수정 (로그인 필요, owner 또는 admin)
     *
     * PATCH /api/v1/posts/{postId}
     *
     * @param UpdatePostRequest $request
     * @param int               $postId
     * @return JsonResponse
     */
    public function update(UpdatePostRequest $request, int $postId): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
    
        $updated = $this->postService->updateById($user, $postId, $data);
    
        return $this->success(new PostResource($updated));
    }

    /**
     * 게시글 삭제 (로그인 필요, owner 또는 admin, soft delete)
     *
     * DELETE /api/v1/posts/{postId}
     *
     * @param Request $request
     * @param int     $postId
     * @return JsonResponse
     */
    public function destroy(Request $request, int $postId): JsonResponse
    {
        $user = $request->user();
    
        $this->postService->destroyById($user, $postId);
    
        return $this->noContent();
    }

    /**
     * 내 게시글 목록 (로그인 필요)
     *
     * GET /api/v1/me/posts?page=&size=&sort=
     *
     * @param IndexPostRequest $request
     * @return JsonResponse
     */
    public function myIndex(IndexPostRequest $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->validated();

        $paginator = $this->postService->paginateMy($user, $filters);

        return $this->paginated($paginator, PostResource::class);
    }

    /**
     * 특정 유저 게시글 목록 (로그인 필요 버전)
     *
     * GET /api/v1/users/{userId}/posts?page=&size=&sort=
     *
     * @param IndexPostRequest $request
     * @param int              $userId
     * @return JsonResponse
     */
    public function userIndex(IndexPostRequest $request, int $userId): JsonResponse
    {
        $filters = $request->validated();
        $paginator = $this->postService->paginateByUser($userId, $filters);

        return $this->paginated($paginator, PostResource::class);
    }
}