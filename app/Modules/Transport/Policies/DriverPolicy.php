<?php

namespace App\Modules\Transport\Policies;

use App\Models\User;
use App\Modules\Transport\Models\Driver;

class DriverPolicy
{
    public function viewAny(User $user): bool { return $user->can('transport.view'); }

    public function create(User $user): bool { return $user->can('transport.create'); }

    public function update(User $user, Driver $driver): bool { return $user->can('transport.update'); }

    public function delete(User $user, Driver $driver): bool { return $user->can('transport.delete') && ! $driver->routes()->exists(); }
}
