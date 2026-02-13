<?php

namespace App\Services\Community;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\PostLikeRepository;
use Illuminate\Support\Facades\DB;

class PostLikeService
{
    public function __construct(
        private readonly PostLikeRepository $postLikeRepository,
    ) {
    }

    /**
     * 게시글 좋아요 추가
     *
     * 이미 좋아요가 되어 있으면 그대로 성공 처리
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function like(Post $post, User $user): void
    {
        DB::transaction(function () use ($post, $user) {

            $already = $this->postLikeRepository->exists($post, $user);

            $this->postLikeRepository->create($post, $user);

            if (!$already && $this->hasLikeCountColumn($post)) {
                $post->increment('like_count');
            }
        });
    }

    /**
     * 게시글 좋아요 취소
     *
     * 좋아요가 되어 있지 않아도 성공 처리
     *
     * @param Post $post
     * @param User $user
     * @return void
     */
    public function unlike(Post $post, User $user): void
    {
        DB::transaction(function () use ($post, $user) {

            $deleted = $this->postLikeRepository->delete($post, $user);

            if ($deleted > 0 && $this->hasLikeCountColumn($post)) {
                $post->decrement('like_count');
            }
        });
    }

    /**
     * 내 게시글 좋아요 여부 조회
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function isLikedByMe(Post $post, User $user): bool
    {
        return $this->postLikeRepository->exists($post, $user);
    }

    /**
     * 게시글 좋아요 개수 조회
     *
     * like_count 컬럼이 존재하면 해당 값을 반환 후
     * 없으면 post_likes 테이블을 count 
     *
     * @param Post $post
     * @return int
     */
    public function count(Post $post): int
    {
        if ($this->hasLikeCountColumn($post)) {
            return (int) ($post->like_count ?? 0);
        }

        return $this->postLikeRepository->countByPost($post);
    }

    /**
     * Post 모델에 like_count 컬럼이 존재하는지 확인
     *
     * @param Post $post
     * @return bool
     */
    private function hasLikeCountColumn(Post $post): bool
    {
        return array_key_exists('like_count', $post->getAttributes())
            || $post->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($post->getTable(), 'like_count');
    }
}