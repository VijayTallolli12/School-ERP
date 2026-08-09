<?php

namespace App\Core\Tenant;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Support\Carbon;

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

    public function getAcademicYearId(): ?int
    {
        return AcademicYear::query()
            ->where('school_id', $this->id())
            ->where('status', 'active')
            ->value('id');
    }

    public function timezone(): string
    {
        $school = $this->school();

        return $school?->timezone ?: config('app.timezone', 'UTC');
    }

    public function now(): Carbon
    {
        return Carbon::now($this->timezone());
    }

    public function startOfToday(): Carbon
    {
        return Carbon::now($this->timezone())->startOfDay();
    }
}
