<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * 게시글 조회 권한
     *
     * 현재는 공개 게시글이므로 모두 허용.
     *
     * @param User|null $user
     * @param Post      $post
     * @return bool
     */
    public function view(?User $user, Post $post): bool
    {
        return true;
    }

    /**
     * 게시글 생성 권한
     *
     * 로그인 사용자만 가능.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->is_active ?? true;
    }

    /**
     * 게시글 수정 권한
     *
     * 작성자 또는 관리자 가능.
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function update(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post) || $this->isAdmin($user);
    }

    /**
     * 게시글 삭제 권한
     *
     * 작성자 또는 관리자 가능.
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->isOwner($user, $post) || $this->isAdmin($user);
    }

    /**
     * 작성자 여부 확인
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    protected function isOwner(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * 관리자 여부 확인
     *
     * @param User $user
     * @return bool
     */
    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}