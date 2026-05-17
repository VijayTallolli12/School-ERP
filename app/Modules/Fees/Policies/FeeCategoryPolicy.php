<?php

namespace App\Modules\Fees\Policies;

use App\Models\User;
use App\Modules\Fees\Models\FeeCategory;

class FeeCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fees.view');
    }

    public function view(User $user, FeeCategory $feeCategory): bool
    {
        return $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fees.create');
    }

    public function update(User $user, FeeCategory $feeCategory): bool
    {
        return $user->can('fees.update');
    }

    public function delete(User $user, FeeCategory $feeCategory): bool
    {
        return $user->can('fees.delete');
    }
}
