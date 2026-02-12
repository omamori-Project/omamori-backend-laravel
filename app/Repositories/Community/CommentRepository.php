<?php

namespace App\Repositories\Community;

use App\Models\Comment;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommentRepository extends BaseRepository
{
    /**
     * 기본 모델 반환
     * @return Model
     */
    protected function getModel(): Model
    {
        return new Comment();
    }

    /**
     * 댓글/답글 단건 조회 (연관관계 포함)
     *
     * @param int $commentId
     * @return Comment|null
     */
    public function findWithRelations(int $commentId): ?Comment
    {
        /** @var Comment|null $comment */
        $comment = $this->getModel()
            ->newQuery()
            ->with(['user', 'post', 'parent'])
            ->whereKey($commentId)
            ->first();

        return $comment;
    }

    /**
     * 게시글 댓글 목록 조회 (공개)
     *
     * 기본 정책:
     * - parent_id = null 인 댓글만 페이징
     * - 각 댓글에 children(답글)을 함께 로드
     *
     * @param int $postId
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateByPost(int $postId, array $filters): LengthAwarePaginator
    {
        $query = $this->getModel()
            ->newQuery()
            ->with([
                'user',
                'children.user',
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id');

        $this->applySorting($query, $filters);

        $page = (int) ($filters['page'] ?? 1);
        $size = (int) ($filters['size'] ?? 20);

        return $query->paginate(
            perPage: $size,
            page: $page
        );
    }

    /**
     * 내 댓글/답글 목록 조회 (로그인)
     *
     * 필터:
     * - type: comment|reply (없으면 전체)
     * - postId: 특정 게시글 기준 필터
     * - sort: latest|oldest
     *
     * @param int $userId
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function paginateMy(int $userId, array $filters): LengthAwarePaginator
    {
        $query = $this->getModel()
            ->newQuery()
            ->with(['post'])
            ->where('user_id', $userId);

        $type = $filters['type'] ?? null;

        if ($type === 'comment') {
            $query->whereNull('parent_id');
        } elseif ($type === 'reply') {
            $query->whereNotNull('parent_id');
        }

        if (isset($filters['postId'])) {
            $query->where('post_id', (int) $filters['postId']);
        }

        $this->applySorting($query, $filters);

        $page = (int) ($filters['page'] ?? 1);
        $size = (int) ($filters['size'] ?? 20);

        return $query->paginate(
            perPage: $size,
            page: $page
        );
    }

    /**
     * 정렬 조건 적용
     *
     * sort 값:
     * - latest : 최신순(created_at desc)
     * - oldest : 오래된순(created_at asc)
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

            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }
}