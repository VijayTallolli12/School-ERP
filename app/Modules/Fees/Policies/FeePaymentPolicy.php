<?php

namespace App\Modules\Fees\Policies;

use App\Models\User;
use App\Modules\Fees\Models\FeePayment;

class FeePaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fees.view');
    }

    public function view(User $user, FeePayment $feePayment): bool
    {
        return $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fees.collect');
    }

    public function delete(User $user, FeePayment $feePayment): bool
    {
        return $user->can('fees.delete');
    }
}
