<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    public function view(User $user, User $actionUser): bool
    {
        return $user->can('view users');
    }

    public function create(User $user): bool
    {
        return $user->can('add users');
    }

    public function update(User $user, User $actionUser): bool
    {
        return $user->can('edit users');
    }

    public function delete(User $user, User $actionUser): bool
    {
        return $user->can('delete users');
    }
}
