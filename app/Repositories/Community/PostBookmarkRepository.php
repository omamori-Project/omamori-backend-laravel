<?php

namespace App\Repositories\Community;

use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostBookmarkRepository
{
    /**
     * 게시글 북마크 여부 확인
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function exists(Post $post, User $user): bool
    {
        return PostBookmark::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * 게시글 북마크 생성
     *
     * 이미 존재하면 그대로 유지
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function create(Post $post, User $user): void
    {
        PostBookmark::query()->firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 게시글 북마크 삭제
     *
     * @param Post $post
     * @param User $user
     * @return int 삭제된 레코드 수
     */
    public function delete(Post $post, User $user): int
    {
        return PostBookmark::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * 내 북마크 게시글 목록 조회
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateByUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return PostBookmark::query()
            ->where('user_id', $user->id)
            ->with('post')
            ->latest('created_at')
            ->paginate($perPage);
    }
}