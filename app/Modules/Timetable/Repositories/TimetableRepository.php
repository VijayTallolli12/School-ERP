<?php

namespace App\Modules\Timetable\Repositories;

use App\Modules\Academics\Models\ClassSection;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Database\Eloquent\Builder;

class TimetableRepository implements TimetableRepositoryInterface
{
    public function query(): Builder
    {
        return TimetableSlot::query()
            ->select('timetable_slots.*')
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject', 'teacher']);
    }

    public function create(array $data): TimetableSlot
    {
        return TimetableSlot::query()->create($data);
    }

    public function update(TimetableSlot $slot, array $data): TimetableSlot
    {
        $slot->fill($data)->save();

        return $slot->refresh();
    }

    public function delete(TimetableSlot $slot): void
    {
        $slot->delete();
    }

    public function filterQuery(Builder $query, array $filters): Builder
    {
        if (! empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (! empty($filters['class_section_id'])) {
            $query->where('class_section_id', $filters['class_section_id']);
        }

        if (! empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (! empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function findTeacherConflicts(array $data, ?int $ignoreId = null): Builder
    {
        $query = $this->query()
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('academic_year_id', $data['academic_year_id']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->where(function (Builder $query) use ($data) {
            $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                ->orWhere(function (Builder $query) use ($data) {
                    $query->where('start_time', '<=', $data['start_time'])
                        ->where('end_time', '>=', $data['end_time']);
                });
        });
    }

    public function findClassSectionConflicts(array $data, ?int $ignoreId = null): Builder
    {
        $query = $this->query()
            ->where('class_section_id', $data['class_section_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('academic_year_id', $data['academic_year_id']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->where(function (Builder $query) use ($data) {
            $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                ->orWhere(function (Builder $query) use ($data) {
                    $query->where('start_time', '<=', $data['start_time'])
                        ->where('end_time', '>=', $data['end_time']);
                });
        });
    }

    public function getForClassSchedule(int $classSectionId, int $academicYearId): Builder
    {
        return $this->query()
            ->where('class_section_id', $classSectionId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('period_number');
    }

    public function getForTeacherSchedule(int $teacherId, int $academicYearId): Builder
    {
        return $this->query()
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('period_number');
    }

    public function getTodaySchedules(int $academicYearId, int $dayOfWeek): Builder
    {
        return $this->query()
            ->where('academic_year_id', $academicYearId)
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'active')
            ->orderBy('period_number');
    }
}
