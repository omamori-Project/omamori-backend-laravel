<?php

namespace App\Repositories\Community;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;

class PostLikeRepository
{
    /**
     * 게시글 좋아요 여부 확인
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function exists(Post $post, User $user): bool
    {
        return PostLike::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * 게시글 좋아요 생성
     *
     * 이미 존재하면 그대로 유지
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function create(Post $post, User $user): void
    {
        PostLike::query()->firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 게시글 좋아요 삭제
     *
     * @param Post $post
     * @param User $user
     * @return int 삭제된 레코드 수
     */
    public function delete(Post $post, User $user): int
    {
        return PostLike::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * 게시글 좋아요 개수 조회
     *
     * @param Post $post
     * @return int
     */
    public function countByPost(Post $post): int
    {
        return PostLike::query()
            ->where('post_id', $post->id)
            ->count();
    }
}