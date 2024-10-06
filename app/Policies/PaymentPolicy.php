<?php

namespace App\Policies;

use App\Models\Partition;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payments');
    }

    public function view(User $user, Payment $payment): bool
    {
        if (!$user->can('view units others')) {
            if ($payment->contract->contractable_type == Unit::class && $payment->contract->contractable->user_id != $user->id) {
                return false;
            } else if ($payment->contract->contractable_type == Partition::class && $payment->contract->contractable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('view payments');
    }

    public function create(User $user): bool
    {
        return $user->can('add payments');
    }

    public function delete(User $user, Payment $payment): bool
    {
        if (!$user->can('view units others')) {
            if ($payment->contract->contractable_type == Unit::class && $payment->contract->contractable->user_id != $user->id) {
                return false;
            } else if ($payment->contract->contractable_type == Partition::class && $payment->contract->contractable->unit->user_id != $user->id) {
                return false;
            }
        }

        return $user->can('delete payments');
    }
}
