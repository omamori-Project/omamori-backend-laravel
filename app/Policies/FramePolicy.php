<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Frame;
use App\Models\User;

class FramePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(?User $user, Frame $frame): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Frame $frame): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Frame $frame): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Frame $frame): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Frame $frame): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}