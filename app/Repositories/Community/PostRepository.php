<?php

namespace App\Repositories\Community;

use App\Models\Post;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PostRepository extends BaseRepository
{
    /**
     * Repository가 사용할 모델 인스턴스 반환
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return new Post();
    }

    /**
     * 게시글 단건 조회 (연관관계 포함)
     *
     * @param int $postId
     * @return Post|null
     */
    public function findWithRelations(int $postId): ?Post
    {
        /** @var Post|null $post */
        $post = $this->getModel()
            ->newQuery()
            ->with(['user', 'omamori'])
            ->whereKey($postId)
            ->first();

        return $post;
    }

    /**
     * 공개 피드 목록 페이지네이션
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateFeed(array $filters): LengthAwarePaginator
    {
        $query = $this->getModel()
            ->newQuery()
            ->with(['user']);

        $this->applySorting($query, $filters);

        $page = (int) ($filters['page'] ?? 1);
        $size = (int) ($filters['size'] ?? 10);

        return $query->paginate(
            perPage: $size,
            page: $page
        );
    }

    /**
     * 특정 유저 게시글 목록 페이지네이션
     *
     * @param int $userId
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateByUser(int $userId, array $filters): LengthAwarePaginator
    {
        $query = $this->getModel()
            ->newQuery()
            ->with(['user'])
            ->where('user_id', $userId);

        $this->applySorting($query, $filters);

        $page = (int) ($filters['page'] ?? 1);
        $size = (int) ($filters['size'] ?? 10);

        return $query->paginate(
            perPage: $size,
            page: $page
        );
    }

    /**
     * 조회수 증가
     *
     * @param Post $post
     * @return void
     */
    public function incrementViewCount(Post $post): void
    {
        $post->increment('view_count');
    }

    /**
     * 정렬 조건 적용
     *
     * sort 값:
     * - latest  : 최신순(created_at desc)
     * - oldest  : 오래된순(created_at asc)
     * - popular : 좋아요 많은 순(like_count desc, created_at desc)
     *
     * @param Builder $query
     * @param array<string, mixed> $filters
     * @return void
     */
    protected function applySorting(Builder $query, array $filters): void
    {
        $sort = (string) ($filters['sort'] ?? 'latest');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'popular':
                $query->orderBy('like_count', 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }
}