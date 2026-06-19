<?php

namespace App\Modules\Transport\Policies;

use App\Models\User;
use App\Modules\Transport\Models\TransportAssignment;

class TransportAssignmentPolicy
{
    public function viewAny(User $user): bool { return $user->can('transport.view'); }

    public function create(User $user): bool { return $user->can('transport.create'); }

    public function update(User $user, TransportAssignment $assignment): bool { return $user->can('transport.update'); }

    public function delete(User $user, TransportAssignment $assignment): bool { return $user->can('transport.delete'); }
}
