<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Comment\IndexCommentRequest;
use App\Http\Requests\Community\Comment\IndexMyCommentRequest;
use App\Http\Requests\Community\Comment\StoreCommentRequest;
use App\Http\Requests\Community\Comment\StoreReplyRequest;
use App\Http\Requests\Community\Comment\UpdateCommentRequest;
use App\Http\Resources\Community\Comment\CommentResource;
use App\Http\Resources\Community\Comment\CommentWithChildrenResource;
use App\Services\Community\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @var CommentService
     */
    protected CommentService $commentService;

    /**
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * 게시글 댓글 목록 조회 (공개)
     *
     * GET /api/v1/posts/{postId}/comments?page=&size=&sort=
     *
     * @param int               $postId
     * @param IndexCommentRequest $request
     * @return JsonResponse
     */
    public function index(int $postId, IndexCommentRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $paginator = $this->commentService->paginateByPost($postId, $filters);

        return $this->paginated($paginator, CommentWithChildrenResource::class);
    }

    /**
     * 댓글 작성 (로그인 필요)
     *
     * POST /api/v1/posts/{postId}/comments
     *
     * @param int               $postId
     * @param StoreCommentRequest $request
     * @return JsonResponse
     */
    public function store(int $postId, StoreCommentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $comment = $this->commentService->store($user, $postId, $data);

        return $this->created(new CommentResource($comment));
    }

    /**
     * 답글 작성 (로그인 필요)
     *
     * POST /api/v1/comments/{commentId}/replies
     *
     * @param int              $commentId
     * @param StoreReplyRequest $request
     * @return JsonResponse
     */
    public function storeReply(int $commentId, StoreReplyRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $reply = $this->commentService->storeReply($user, $commentId, $data);

        return $this->created(new CommentResource($reply));
    }

    /**
     * 댓글/답글 수정 (로그인 필요, owner 또는 admin)
     *
     * PATCH /api/v1/comments/{commentId}
     *
     * @param int                $commentId
     * @param UpdateCommentRequest $request
     * @return JsonResponse
     */
    public function update(int $commentId, UpdateCommentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $updated = $this->commentService->updateById($user, $commentId, $data);

        return $this->success(new CommentResource($updated));
    }

    /**
     * 댓글/답글 삭제 (로그인 필요, owner 또는 admin, soft delete)
     *
     * DELETE /api/v1/comments/{commentId}
     *
     * @param int     $commentId
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $commentId, Request $request): JsonResponse
    {
        $user = $request->user();

        $this->commentService->destroyById($user, $commentId);

        return $this->noContent();
    }

    /**
     * 내 댓글/답글 목록 조회 (로그인 필요)
     *
     * GET /api/v1/me/comments?page=&size=&sort=&type=&postId=
     *
     * @param IndexMyCommentRequest $request
     * @return JsonResponse
     */
    public function myIndex(IndexMyCommentRequest $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->validated();

        $paginator = $this->commentService->paginateMy($user, $filters);

        return $this->paginated($paginator, CommentResource::class);
    }
}