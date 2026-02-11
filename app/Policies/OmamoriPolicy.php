<?php

namespace App\Policies;

use App\Models\Omamori;
use App\Models\User;

class OmamoriPolicy
{
    /**
     * 조회 - 본인 것은 항상 볼 수 있음, 다른 사람 것은 공개된 것만
     */
    public function view(User $user, Omamori $omamori): bool
    {
        if ($user->id === $omamori->user_id) {
            return true;
        }

        return $omamori->status === 'published';
    }

    /**
     * 수정 - 본인 것만
     */
    public function update(User $user, Omamori $omamori): bool
    {
        return $user->id === $omamori->user_id;
    }

    /**
     * 삭제 - 본인 것만
     */
    public function delete(User $user, Omamori $omamori): bool
    {
        return $user->id === $omamori->user_id;
    }
}