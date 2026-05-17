<?php

namespace App\Modules\Timetable\Repositories;

use App\Modules\Academics\Models\ClassSection;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Database\Eloquent\Builder;

interface TimetableRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): TimetableSlot;

    public function update(TimetableSlot $slot, array $data): TimetableSlot;

    public function delete(TimetableSlot $slot): void;

    public function filterQuery(Builder $query, array $filters): Builder;

    public function findTeacherConflicts(array $data, ?int $ignoreId = null): Builder;

    public function findClassSectionConflicts(array $data, ?int $ignoreId = null): Builder;

    public function getForClassSchedule(int $classSectionId, int $academicYearId): Builder;

    public function getForTeacherSchedule(int $teacherId, int $academicYearId): Builder;

    public function getTodaySchedules(int $academicYearId, int $dayOfWeek): Builder;
}
