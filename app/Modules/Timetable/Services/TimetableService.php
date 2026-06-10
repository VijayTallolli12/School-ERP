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

    public function duplicateDay(array $data): array
    {
        $sourceSlots = $this->timetable->query()
            ->where('class_section_id', $data['class_section_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('day_of_week', $data['source_day'])
            ->get();

        if ($sourceSlots->isEmpty()) {
            return ['created' => 0, 'skipped' => 0, 'errors' => [], 'message' => 'No slots found on the source day.'];
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        $targetDay = (int) $data['target_day'];
        $classSectionId = (int) $data['class_section_id'];
        $academicYearId = (int) $data['academic_year_id'];

        foreach ($sourceSlots as $slot) {
            $slotData = [
                'teacher_id' => $slot->teacher_id,
                'class_section_id' => $classSectionId,
                'subject_id' => $slot->subject_id,
                'academic_year_id' => $academicYearId,
                'day_of_week' => $targetDay,
                'period_number' => $slot->period_number,
                'period_label' => $slot->period_label,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'room' => $slot->room,
                'status' => $slot->status,
            ];

            $conflict = $this->checkConflicts($slotData);
            if ($conflict) {
                $skipped++;
                $errors[] = "{$slotData['period_label']}: {$conflict}";
                continue;
            }

            $this->timetable->create([
                ...$slotData,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Duplicated {$created} slot(s). Skipped {$skipped} due to conflicts.",
        ];
    }

    public function copyTimetable(array $data): array
    {
        $sourceSlots = $this->timetable->query()
            ->where('class_section_id', $data['source_class_section_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->get();

        if ($sourceSlots->isEmpty()) {
            return ['created' => 0, 'skipped' => 0, 'errors' => [], 'message' => 'No slots found in the source class.'];
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $adjustRoom = ! empty($data['adjust_room_names']);

        $targetClassSectionId = (int) $data['target_class_section_id'];
        $academicYearId = (int) $data['academic_year_id'];

        foreach ($sourceSlots as $slot) {
            $room = $slot->room;
            if ($adjustRoom && $room) {
                $room = $room . ' (copied)';
            }

            $slotData = [
                'teacher_id' => $slot->teacher_id,
                'class_section_id' => $targetClassSectionId,
                'subject_id' => $slot->subject_id,
                'academic_year_id' => $academicYearId,
                'day_of_week' => $slot->day_of_week,
                'period_number' => $slot->period_number,
                'period_label' => $slot->period_label,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'room' => $room,
                'status' => $slot->status,
            ];

            $conflict = $this->checkConflicts($slotData);
            if ($conflict) {
                $skipped++;
                $errors[] = "{$slotData['period_label']} (Day {$slotData['day_of_week']}): {$conflict}";
                continue;
            }

            $this->timetable->create([
                ...$slotData,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Copied {$created} slot(s). Skipped {$skipped} due to conflicts.",
        ];
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
        $conflict = $this->checkConflicts($data, $ignoreId);
        if ($conflict) {
            $messages = [];

            if (str_contains($conflict, 'teacher')) {
                $messages['teacher_id'] = [$conflict];
            } elseif (str_contains($conflict, 'room')) {
                $messages['room'] = [$conflict];
            } else {
                $messages['class_section_id'] = [$conflict];
            }

            throw ValidationException::withMessages($messages);
        }
    }

    private function checkConflicts(array $data, ?int $ignoreId = null): ?string
    {
        if ($this->timetable->findTeacherConflicts($data, $ignoreId)->exists()) {
            return 'This teacher is already assigned to another class during this time slot.';
        }

        if ($this->timetable->findClassSectionConflicts($data, $ignoreId)->exists()) {
            return 'This class section already has a period scheduled during this time.';
        }

        if ($this->timetable->findRoomConflicts($data, $ignoreId)->exists()) {
            return 'This room is already booked for another class during this time slot.';
        }

        return null;
    }
}
