<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * 댓글 조회 권한
     * 공개 댓글이므로 모두 허용
     *
     * @param User|null $user
     * @param Comment   $comment
     * @return bool
     */
    public function view(?User $user, Comment $comment): bool
    {
        return true;
    }

    /**
     * 댓글 생성 권한
     *
     * 로그인 사용자만 가능
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->is_active ?? true;
    }

    /**
     * 댓글 수정 권한
     *
     * 작성자 또는 관리자 가능
     *
     * @param User    $user
     * @param Comment $comment
     * @return bool
     */
    public function update(User $user, Comment $comment): bool
    {
        return $this->isOwner($user, $comment) || $this->isAdmin($user);
    }

    /**
     * 댓글 삭제 권한
     *
     * 작성자 또는 관리자 가능
     *
     * @param User    $user
     * @param Comment $comment
     * @return bool
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->isOwner($user, $comment) || $this->isAdmin($user);
    }

    /**
     * 작성자 여부 확인
     *
     * @param User    $user
     * @param Comment $comment
     * @return bool
     */
    protected function isOwner(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
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