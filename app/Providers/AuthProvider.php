<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Partition;
use App\Models\Payment;
use App\Models\SecurityDeposit;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Policies\BuildingPolicy;
use App\Policies\ContractPolicy;
use App\Policies\PartitionPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RolePolicy;
use App\Policies\SecurityDepositPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AuthProvider extends AuthServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Building::class => BuildingPolicy::class,
        Unit::class => UnitPolicy::class,
        Partition::class => PartitionPolicy::class,
        Tenant::class => TenantPolicy::class,
        SecurityDeposit::class => SecurityDepositPolicy::class,
        Contract::class => ContractPolicy::class,
        Payment::class => PaymentPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}
