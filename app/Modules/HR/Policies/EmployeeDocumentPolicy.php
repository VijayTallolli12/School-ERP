<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\EmployeeDocument;

class EmployeeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.view');
    }

    public function view(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.create');
    }

    public function update(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.update');
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.delete');
    }

    public function verify(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.verify');
    }
}
