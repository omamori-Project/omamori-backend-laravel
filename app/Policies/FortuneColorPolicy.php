<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FortuneColor;
use App\Models\User;

class FortuneColorPolicy
{
    /**
     * 관리자만 목록 조회 가능 (Admin 영역)
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * 단건 조회는 Public 허용
     */
    public function view(?User $user, FortuneColor $fortuneColor): bool
    {
        return true;
    }

    /**
     * 생성은 관리자만
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * 수정은 관리자만
     */
    public function update(User $user, FortuneColor $fortuneColor): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * 삭제는 관리자만 (Soft Delete)
     */
    public function delete(User $user, FortuneColor $fortuneColor): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * 복구는 관리자만
     */
    public function restore(User $user, FortuneColor $fortuneColor): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * 영구 삭제는 관리자만
     */
    public function forceDelete(User $user, FortuneColor $fortuneColor): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}