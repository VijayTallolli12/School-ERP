<?php

namespace App\Modules\Academics\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Academics\Repositories\AcademicRepositoryInterface;

class ClassesService
{
    public function __construct(
        private readonly AcademicRepositoryInterface $academics,
        private readonly SchoolContext $schoolContext
    ) {
    }

    public function getAllClasses()
    {
        return $this->academics->classes()
            ->where('school_id', $this->schoolContext->id())
            ->get();
    }
}
