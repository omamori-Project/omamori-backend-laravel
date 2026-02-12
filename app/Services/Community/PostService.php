<?php

namespace App\Services\Community;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\PostRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class PostService extends BaseService
{
    /**
     * @var PostRepository
     */
    protected PostRepository $postRepository;

    /**
     * @param PostRepository $postRepository
     */
    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * 공개 피드 목록 조회
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateFeed(array $filters): LengthAwarePaginator
    {
        return $this->postRepository->paginateFeed($filters);
    }

    /**
     * 특정 유저 게시글 목록 조회
     *
     * @param int $userId
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateByUser(int $userId, array $filters): LengthAwarePaginator
    {
        return $this->postRepository->paginateByUser($userId, $filters);
    }

    /**
     * 내 게시글 목록 조회
     *
     * @param User $user
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateMy(User $user, array $filters): LengthAwarePaginator
    {
        return $this->postRepository->paginateByUser($user->id, $filters);
    }

    /**
     * 게시글 상세 조회
     *
     * @param int  $postId
     * @param bool $increaseViewCount 조회수 증가 여부
     * @return Post
     */
    public function show(int $postId, bool $increaseViewCount = true): Post
    {
        return $this->transaction(function () use ($postId, $increaseViewCount): Post {
            $post = $this->findOrFailWithRelations($postId);

            if ($increaseViewCount) {
                $this->postRepository->incrementViewCount($post);
                $post->refresh();
            }

            return $post;
        });
    }

    /**
     * 게시글 작성
     *
     * @param User                 $user
     * @param array<string, mixed> $data
     * @return Post
     */
    public function store(User $user, array $data): Post
    {
        return $this->transaction(function () use ($user, $data): Post {
            $payload = array_merge($data, [
                'user_id' => $user->id,
            ]);

            /** @var Post $post */
            $post = $this->postRepository->create($payload);

            $post->load(['user', 'omamori']);

            return $post;
        });
    }

    /**
     * 게시글 수정
     *
     * @param User                 $user
     * @param int                  $postId
     * @param array<string, mixed> $data
     * @return Post
     */
    public function updateById(User $user, int $postId, array $data): Post
    {
        $post = $this->findOrFailWithRelations($postId);
        Gate::forUser($user)->authorize('update', $post);

        return $this->transaction(function () use ($post, $data): Post {
            $this->postRepository->update($post, $data);

            $post->refresh()->load(['user', 'omamori']);

            return $post;
        });
    }

    /**
     * 게시글 삭제 (Soft Delete)
     *
     * @param User $user
     * @param int  $postId
     * @return void
     */
    public function destroyById(User $user, int $postId): void
    {
        $post = $this->findOrFailWithRelations($postId);
        Gate::forUser($user)->authorize('delete', $post);

        $this->transaction(function () use ($post): void {
            $this->postRepository->delete($post);
        });
    }

    /**
     * 게시글 단건 조회 - 없으면 404 처리용 예외 발생
     *
     * @param int $postId
     * @return Post
     *
     * @throws ModelNotFoundException
     */
    protected function findOrFailWithRelations(int $postId): Post
    {
        $post = $this->postRepository->findWithRelations($postId);

        if ($post === null) {
            throw new ModelNotFoundException('Post not found.');
        }

        return $post;
    }
}