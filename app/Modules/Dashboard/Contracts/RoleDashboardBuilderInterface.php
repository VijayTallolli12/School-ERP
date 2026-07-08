<?php

namespace App\Modules\Dashboard\Contracts;

use App\Models\User;
use App\Modules\Dashboard\DTOs\DashboardView;

interface RoleDashboardBuilderInterface
{
    public function build(User $user, int $schoolId): DashboardView;

    public function getRoleName(): string;

    public function getLayout(): string;
}
