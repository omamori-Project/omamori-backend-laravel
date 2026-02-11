<?php

namespace App\Policies;

use App\Models\Share;
use App\Models\User;

class SharePolicy
{
    /**
     * 관리자면 전부 허용
     */
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    /**
     * share 단건 조회(로그인 영역에서 share 확인할 때)
     */
    public function view(User $user, Share $share): bool
    {
        return (int) $share->user_id === (int) $user->id;
    }

    /**
     * share 설정 변경(PATCH /shares/{shareId})
     */
    public function update(User $user, Share $share): bool
    {
        return (int) $share->user_id === (int) $user->id;
    }

    /**
     * share 삭제(DELETE /shares/{shareId})
     */
    public function delete(User $user, Share $share): bool
    {
        return (int) $share->user_id === (int) $user->id;
    }

}