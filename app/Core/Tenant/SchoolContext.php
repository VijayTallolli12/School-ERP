<?php

namespace App\Core\Tenant;

use App\Models\School;

class SchoolContext
{
    private ?int $schoolId = null;

    private ?School $school = null;

    public function set(?int $schoolId): void
    {
        $this->schoolId = $schoolId;
        $this->school = null;
    }

    public function id(): ?int
    {
        return $this->schoolId;
    }

    public function school(): ?School
    {
        if (! $this->schoolId) {
            return null;
        }

        return $this->school ??= School::query()->find($this->schoolId);
    }
}
