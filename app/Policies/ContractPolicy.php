<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\Partition;
use App\Models\Unit;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contracts');
    }

    public function view(User $user, Contract $contract): bool
    {
        if (!$user->can('view units others')) {
            if ($contract->contractable_type == Unit::class && $contract->contractable->user_id != $user->id) {
                return false;
            } else if ($contract->contractable_type == Partition::class && $contract->contractable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('view contracts');
    }

    public function create(User $user): bool
    {
        return $user->can('add contracts');
    }

    public function delete(User $user, Contract $contract): bool
    {
        if (!$user->can('view units others')) {
            if ($contract->contractable_type == Unit::class && $contract->contractable->user_id != $user->id) {
                return false;
            } else if ($contract->contractable_type == Partition::class && $contract->contractable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('delete contracts');
    }
}
