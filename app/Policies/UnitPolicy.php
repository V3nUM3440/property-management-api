<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view units') || $user->can('view units others');
    }

    public function view(User $user, Unit $unit): bool
    {
        if (!$user->can('view units others') && $unit->user_id != $user->id) {
            return false;
        }
        return $user->can('view units');
    }

    public function create(User $user): bool
    {
        return $user->can('add units');
    }

    public function update(User $user, Unit $unit): bool
    {
        if (!$user->can('edit units others') && $unit->user_id != $user->id) {
            return false;
        }
        return $user->can('edit units');
    }

    public function delete(User $user, Unit $unit): bool
    {
        if (!$user->can('delete units others') && $unit->user_id != $user->id) {
            return false;
        }
        return $user->can('delete units');
    }
}
