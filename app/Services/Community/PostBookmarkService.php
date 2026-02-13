<?php

namespace App\Services\Community;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\PostBookmarkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostBookmarkService
{
    public function __construct(
        private readonly PostBookmarkRepository $postBookmarkRepository,
    ) {
    }

    /**
     * 게시글 북마크 추가
     *
     * 이미 북마크가 되어 있으면 그대로 성공 처리
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function bookmark(Post $post, User $user): void
    {
        $this->postBookmarkRepository->create($post, $user);
    }

    /**
     * 게시글 북마크 취소
     *
     * 북마크가 되어 있지 않아도 성공 처리
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function unbookmark(Post $post, User $user): void
    {
        $this->postBookmarkRepository->delete($post, $user);
    }

    /**
     * 내 게시글 북마크 여부 조회
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function isBookmarkedByMe(Post $post, User $user): bool
    {
        return $this->postBookmarkRepository->exists($post, $user);
    }

    /**
     * 내 북마크 게시글 목록 조회
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function myBookmarks(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $this->postBookmarkRepository->paginateByUser($user, $perPage);
    }
}