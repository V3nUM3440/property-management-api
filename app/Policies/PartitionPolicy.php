<?php

namespace App\Policies;

use App\Models\Partition;
use App\Models\User;

class PartitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view partitions');
    }

    public function view(User $user, Partition $partition): bool
    {
        if (!$user->can('view units others') && $partition->unit->user_id != $user->id) {
            return false;
        }
        return $user->can('view partitions');
    }

    public function create(User $user): bool
    {
        return $user->can('add partitions');
    }

    public function update(User $user, Partition $partition): bool
    {
        if (!$user->can('view units others') && $partition->unit->user_id != $user->id) {
            return false;
        }
        return $user->can('edit partitions');
    }

    public function delete(User $user, Partition $partition): bool
    {
        if (!$user->can('view units others') && $partition->unit->user_id != $user->id) {
            return false;
        }
        return $user->can('delete partitions');
    }
}
