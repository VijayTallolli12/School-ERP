<?php

namespace App\Modules\Transport\Policies;

use App\Models\User;
use App\Modules\Transport\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool { return $user->can('transport.view'); }

    public function create(User $user): bool { return $user->can('transport.create'); }

    public function update(User $user, Vehicle $vehicle): bool { return $user->can('transport.update'); }

    public function delete(User $user, Vehicle $vehicle): bool { return $user->can('transport.delete'); }
}
