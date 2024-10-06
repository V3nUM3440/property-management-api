<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;

class BuildingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view buildings');
    }

    public function view(User $user, Building $building): bool
    {
        return $user->can('view buildings');
    }

    public function create(User $user): bool
    {
        return $user->can('add buildings');
    }

    public function update(User $user, Building $building): bool
    {
        return $user->can('edit buildings');
    }

    public function delete(User $user, Building $building): bool
    {
        return $user->can('delete buildings');
    }
}
