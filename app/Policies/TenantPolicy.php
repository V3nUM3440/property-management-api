<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view tenants');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->can('view tenants');
    }

    public function create(User $user): bool
    {
        return $user->can('add tenants');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->can('edit tenants');
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->can('delete tenants');
    }
}
