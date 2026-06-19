<?php

namespace App\Modules\Transport\Policies;

use App\Models\User;
use App\Modules\Transport\Models\RouteStop;

class RouteStopPolicy
{
    public function viewAny(User $user): bool { return $user->can('transport.view'); }

    public function create(User $user): bool { return $user->can('transport.create'); }

    public function update(User $user, RouteStop $routeStop): bool { return $user->can('transport.update'); }

    public function delete(User $user, RouteStop $routeStop): bool { return $user->can('transport.delete'); }
}
