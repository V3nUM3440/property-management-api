<?php

namespace App\Policies;

use App\Models\Partition;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;

class SecurityDepositPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view security deposits');
    }

    public function view(User $user, SecurityDeposit $deposit): bool
    {
        if (!$user->can('view units others')) {
            if ($deposit->depositable_type == Unit::class && $deposit->depositable->user_id != $user->id) {
                return false;
            } else if ($deposit->depositable_type == Partition::class && $deposit->depositable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('view security deposits');
    }

    public function create(User $user): bool
    {
        return $user->can('add security deposits');
    }

    public function delete(User $user, SecurityDeposit $deposit): bool
    {
        if (!$user->can('view units others')) {
            if ($deposit->depositable_type == Unit::class && $deposit->depositable->user_id != $user->id) {
                return false;
            } else if ($deposit->depositable_type == Partition::class && $deposit->depositable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('delete security deposits');
    }
}
