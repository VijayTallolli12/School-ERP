<?php

namespace App\Modules\Dashboard\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Dashboard\DTOs\DashboardView;
use Spatie\Permission\PermissionRegistrar;

class DashboardService
{
    public function __construct(
        private readonly DashboardFactory $factory,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function build(User $user, ?int $schoolId = null): DashboardView
    {
        $schoolId ??= $this->schoolContext->id();

        if (! $schoolId) {
            abort(403, 'No school context available.');
        }

        // Re-apply the team ID so DashboardFactory::make()->hasRole()
        // resolves roles correctly regardless of middleware timing.
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $builder = $this->factory->make($user);

        return $builder->build($user, $schoolId);
    }
}
