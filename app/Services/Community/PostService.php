<?php

namespace App\Services\Community;

use App\Models\Omamori;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\PostRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PostService extends BaseService
{
    /**
     * @var PostRepository
     */
    protected PostRepository $postRepository;

    /**
     * @var PostOmamoriSnapshotService
     */
    protected PostOmamoriSnapshotService $snapshotService;

    /**
     * @param PostRepository             $postRepository
     * @param PostOmamoriSnapshotService $snapshotService
     */
    public function __construct(
        PostRepository $postRepository,
        PostOmamoriSnapshotService $snapshotService
    ) {
        $this->postRepository = $postRepository;
        $this->snapshotService = $snapshotService;
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
     * 규칙:
     * - omamori_id 필수
     * - published 오마모리만 첨부 가능
     * - 내 오마모리만 첨부 가능
     * - 게시 시점 오마모리 스냅샷 저장
     * - tags는 nullable
     *
     * @param User                 $user
     * @param array<string, mixed> $data
     * @return Post
     *
     * @throws ValidationException
     */
    public function store(User $user, array $data): Post
    {
        return $this->transaction(function () use ($user, $data): Post {
            $omamoriId = (int) ($data['omamori_id'] ?? 0);

            if ($omamoriId < 1) {
                throw ValidationException::withMessages([
                    'omamori_id' => ['오마모리는 필수 항목입니다.'],
                ]);
            }

            $omamori = $this->resolveAttachableOmamori($user, $omamoriId);

            $payload = [
                'user_id' => $user->id,
                'omamori_id' => $omamori->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'omamori_snapshot' => $this->snapshotService->make($omamori),
                'tags' => $data['tags'] ?? null,
            ];

            /** @var Post $post */
            $post = $this->postRepository->create($payload);

            $post->load(['user', 'omamori']);

            return $post;
        });
    }

    /**
     * 게시글 수정
     *
     * 규칙:
     * - omamori_id 변경 허용
     * - omamori_id가 변경되면 snapshot도 재생성/저장
     * - tags는 nullable
     *
     * @param User                 $user
     * @param int                  $postId
     * @param array<string, mixed> $data
     * @return Post
     *
     * @throws ValidationException
     */
    public function updateById(User $user, int $postId, array $data): Post
    {
        $post = $this->findOrFailWithRelations($postId);
        Gate::forUser($user)->authorize('update', $post);

        return $this->transaction(function () use ($user, $post, $data): Post {
            $omamoriId = (int) ($data['omamori_id'] ?? 0);

            if ($omamoriId < 1) {
                throw ValidationException::withMessages([
                    'omamori_id' => ['오마모리는 필수 항목입니다.'],
                ]);
            }

            $update = [
                'title' => $data['title'] ?? $post->title,
                'content' => $data['content'] ?? $post->content,
                'tags' => $data['tags'] ?? null,
            ];

            if ((int) $post->omamori_id !== $omamoriId) {
                $omamori = $this->resolveAttachableOmamori($user, $omamoriId);

                $update['omamori_id'] = $omamori->id;
                $update['omamori_snapshot'] = $this->snapshotService->make($omamori);
            }

            $this->postRepository->update($post, $update);

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

    /**
     * 게시글에 첨부 가능한 오마모리인지 검증 후 반환
     *
     * 규칙:
     * - 내 오마모리만 가능
     * - published만 가능
     *
     * @param User $user
     * @param int  $omamoriId
     * @return Omamori
     *
     * @throws ValidationException
     */
    protected function resolveAttachableOmamori(User $user, int $omamoriId): Omamori
    {
        $omamori = Omamori::query()->whereKey($omamoriId)->first();

        if (!$omamori) {
            throw ValidationException::withMessages([
                'omamori_id' => ['오마모리를 찾을 수 없습니다.'],
            ]);
        }

        if ((int) $omamori->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'omamori_id' => ['내 오마모리만 첨부할 수 있습니다.'],
            ]);
        }

        if ($omamori->status !== Omamori::STATUS_PUBLISHED) {
            throw ValidationException::withMessages([
                'omamori_id' => ['published 오마모리만 첨부할 수 있습니다.'],
            ]);
        }

        return $omamori;
    }
}