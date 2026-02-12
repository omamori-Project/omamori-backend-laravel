<?php

namespace App\Services\Community;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\CommentRepository;
use App\Repositories\Community\PostRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CommentService extends BaseService
{
    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentRepository;

    /**
     * @var PostRepository
     */
    protected PostRepository $postRepository;

    /**
     * @param CommentRepository $commentRepository
     * @param PostRepository    $postRepository
     */
    public function __construct(
        CommentRepository $commentRepository,
        PostRepository $postRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * 게시글 댓글 목록(공개)
     *
     * @param int $postId
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateByPost(int $postId, array $filters): LengthAwarePaginator
    {
        $this->findOrFailPost($postId);

        return $this->commentRepository->paginateByPost($postId, $filters);
    }

    /**
     * 내 댓글/답글 목록(로그인)
     *
     * @param User $user
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateMy(User $user, array $filters): LengthAwarePaginator
    {
        return $this->commentRepository->paginateMy($user->id, $filters);
    }

    /**
     * 댓글 작성
     *
     * @param User                 $user
     * @param int                  $postId
     * @param array<string, mixed> $data
     * @return Comment
     */
    public function store(User $user, int $postId, array $data): Comment
    {
        return $this->transaction(function () use ($user, $postId, $data): Comment {
            $post = $this->findOrFailPost($postId);

            /** @var Comment $comment */
            $comment = $this->commentRepository->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_id' => null,
                'content' => $data['content'],
            ]);

            $this->incrementPostCommentCount($post, 1);

            $comment->load(['user', 'post']);

            return $comment;
        });
    }

    /**
     * 답글 작성
     *
     * @param User                 $user
     * @param int                  $parentCommentId
     * @param array<string, mixed> $data
     * @return Comment
     */
    public function storeReply(User $user, int $parentCommentId, array $data): Comment
    {
        return $this->transaction(function () use ($user, $parentCommentId, $data): Comment {
            $parent = $this->findOrFailComment($parentCommentId);

            if ($parent->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Reply to a reply is not allowed.'],
                ]);
            }

            $post = $this->findOrFailPost((int) $parent->post_id);

            /** @var Comment $reply */
            $reply = $this->commentRepository->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_id' => $parent->id,
                'content' => $data['content'],
            ]);

            $this->incrementPostCommentCount($post, 1);

            $reply->load(['user', 'post', 'parent']);

            return $reply;
        });
    }

    /**
     * 댓글/답글 수정
     *
     * @param User                 $user
     * @param int                  $commentId
     * @param array<string, mixed> $data
     * @return Comment
     */
    public function updateById(User $user, int $commentId, array $data): Comment
    {
        $comment = $this->findOrFailComment($commentId);
        Gate::forUser($user)->authorize('update', $comment);

        return $this->transaction(function () use ($comment, $data): Comment {
            $this->commentRepository->update($comment, [
                'content' => $data['content'],
            ]);

            $comment->refresh()->load(['user', 'post', 'parent']);

            return $comment;
        });
    }

    /**
     * 댓글/답글 삭제(soft delete)
     *
     * @param User $user
     * @param int  $commentId
     * @return void
     */
    public function destroyById(User $user, int $commentId): void
    {
        $comment = $this->findOrFailComment($commentId);
        Gate::forUser($user)->authorize('delete', $comment);

        $this->transaction(function () use ($comment): void {
            $post = $this->findOrFailPost((int) $comment->post_id);

            $this->commentRepository->delete($comment);

            $this->incrementPostCommentCount($post, -1);
        });
    }

    /**
     * 게시글 단건 조회 - 없으면 404 예외
     *
     * @param int $postId
     * @return Post
     *
     * @throws ModelNotFoundException
     */
    protected function findOrFailPost(int $postId): Post
    {
        $post = $this->postRepository->findWithRelations($postId);

        if ($post === null) {
            throw new ModelNotFoundException('Post not found.');
        }

        return $post;
    }

    /**
     * 댓글 단건 조회 - 없으면 404 예외
     *
     * @param int $commentId
     * @return Comment
     *
     * @throws ModelNotFoundException
     */
    protected function findOrFailComment(int $commentId): Comment
    {
        $comment = $this->commentRepository->findWithRelations($commentId);

        if ($comment === null) {
            throw new ModelNotFoundException('Comment not found.');
        }

        return $comment;
    }

    /**
     * 게시글 comment_count 증감 (댓글 + 답글 합산)
     *
     * @param Post $post
     * @param int  $delta
     * @return void
     */
    protected function incrementPostCommentCount(Post $post, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $post->increment('comment_count', $delta);
    }
}