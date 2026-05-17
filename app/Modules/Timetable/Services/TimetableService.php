<?php

namespace App\Modules\Timetable\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Modules\Timetable\Models\TimetableSlot;
use App\Modules\Timetable\Repositories\TimetableRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableService
{
    public function __construct(
        private readonly TimetableRepositoryInterface $timetable,
        private readonly SchoolContext $schoolContext,
    ) {
    }

    public function createSlot(array $data): TimetableSlot
    {
        $this->validateConflicts($data);

        return DB::transaction(function () use ($data) {
            $payload = $this->payload($data);
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();

            return $this->timetable->create($payload);
        });
    }

    public function updateSlot(TimetableSlot $slot, array $data): TimetableSlot
    {
        $this->validateConflicts($data, $slot->id);

        return DB::transaction(function () use ($slot, $data) {
            $payload = $this->payload($data);
            $payload['updated_by'] = auth()->id();

            return $this->timetable->update($slot, $payload);
        });
    }

    public function deleteSlot(TimetableSlot $slot): void
    {
        $this->timetable->delete($slot);
    }

    public function activeAcademicYear(): ?AcademicYear
    {
        return AcademicYear::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active')
            ->first();
    }

    public function todaySchedulesCount(): int
    {
        $academicYear = $this->activeAcademicYear();

        if (! $academicYear) {
            return 0;
        }

        return $this->timetable->getTodaySchedules($academicYear->id, now()->dayOfWeekIso)->count();
    }

    public function activeClassCount(): int
    {
        $academicYear = $this->activeAcademicYear();

        if (! $academicYear) {
            return 0;
        }

        return $this->timetable->query()
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->distinct()
            ->count('class_section_id');
    }

    private function payload(array $data): array
    {
        return [
            'teacher_id' => $data['teacher_id'],
            'class_section_id' => $data['class_section_id'],
            'subject_id' => $data['subject_id'],
            'academic_year_id' => $data['academic_year_id'],
            'day_of_week' => $data['day_of_week'],
            'period_number' => $data['period_number'],
            'period_label' => $data['period_label'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room' => $data['room'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function validateConflicts(array $data, ?int $ignoreId = null): void
    {
        $teacherConflict = $this->timetable->findTeacherConflicts($data, $ignoreId)->exists();
        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => ['Selected teacher is already assigned to another period at this time.'],
            ]);
        }

        $classConflict = $this->timetable->findClassSectionConflicts($data, $ignoreId)->exists();
        if ($classConflict) {
            throw ValidationException::withMessages([
                'class_section_id' => ['This class section already has a conflicting period at the selected time.'],
            ]);
        }
    }
}
