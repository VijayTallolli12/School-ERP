<?php

namespace App\Modules\Payroll\Policies;

use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool { return $user->can('payroll.view'); }
    public function create(User $user): bool { return $user->can('payroll.create'); }
    public function update(User $user, $model): bool { return $user->can('payroll.update'); }
    public function delete(User $user, $model): bool { return $user->can('payroll.delete'); }
}
