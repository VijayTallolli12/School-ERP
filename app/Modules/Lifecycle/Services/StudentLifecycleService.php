<?php

namespace App\Modules\Lifecycle\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentLifecycleService
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function promote(Student $student, int $toClassSectionId, int $toAcademicYearId, ?string $rollNo = null): StudentTransfer
    {
        return DB::transaction(function () use ($student, $toClassSectionId, $toAcademicYearId, $rollNo): StudentTransfer {
            $this->assertSchoolContext();

            if ($student->sessions()->where('academic_year_id', $toAcademicYearId)->exists()) {
                throw new RuntimeException($student->full_name.' already has a session for the selected academic year.');
            }

            $activeSession = $student->sessions()->where('status', 'active')->latest()->first();

            $fromClassSectionId = $activeSession?->class_section_id;
            $fromAcademicYearId = $activeSession?->academic_year_id;

            if ($activeSession) {
                $activeSession->update([
                    'status' => 'promoted',
                    'left_on' => now()->toDateString(),
                ]);
            }

            $student->sessions()->create([
                'school_id' => $student->school_id,
                'academic_year_id' => $toAcademicYearId,
                'class_section_id' => $toClassSectionId,
                'roll_no' => $rollNo ?: null,
                'joined_on' => now()->toDateString(),
                'status' => 'active',
            ]);

            $student->update([
                'status' => 'active',
                'updated_by' => auth()->id(),
            ]);

            $transfer = StudentTransfer::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'transfer_type' => 'promotion',
                'from_class_section_id' => $fromClassSectionId,
                'to_class_section_id' => $toClassSectionId,
                'from_academic_year_id' => $fromAcademicYearId,
                'to_academic_year_id' => $toAcademicYearId,
                'transferred_on' => now()->toDateString(),
                'status' => 'issued',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->notifyParents($student, 'Promotion', $student->full_name.' has been promoted to a new class.');
            $this->log($student, 'promoted', 'Student promoted to new class');

            return $transfer->load(['student', 'fromClassSection.schoolClass', 'fromClassSection.section', 'toClassSection.schoolClass', 'toClassSection.section']);
        });
    }

    public function bulkPromote(array $studentIds, int $toClassSectionId, int $toAcademicYearId, array $rollNumbers = []): array
    {
        $promoted = 0;
        $skipped = [];

        foreach ($studentIds as $studentId) {
            $student = Student::query()->find($studentId);

            if (! $student) {
                $skipped[] = $studentId;
                continue;
            }

            try {
                $this->promote($student, $toClassSectionId, $toAcademicYearId, $rollNumbers[$studentId] ?? null);
                $promoted++;
            } catch (RuntimeException $e) {
                $skipped[] = $student->full_name;
            }
        }

        return ['promoted' => $promoted, 'skipped' => $skipped];
    }

    public function transfer(Student $student, array $data): StudentTransfer
    {
        return DB::transaction(function () use ($student, $data): StudentTransfer {
            $this->assertSchoolContext();

            if ($student->status === 'transferred') {
                throw new RuntimeException($student->full_name.' is already transferred.');
            }

            $activeSession = $student->sessions()->where('status', 'active')->latest()->first();

            if ($activeSession) {
                $activeSession->update([
                    'status' => 'transferred',
                    'left_on' => $data['transferred_on'] ?? now()->toDateString(),
                ]);
            }

            $student->update([
                'status' => 'transferred',
                'updated_by' => auth()->id(),
            ]);

            $transfer = StudentTransfer::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'transfer_type' => 'transfer',
                'from_class_section_id' => $activeSession?->class_section_id,
                'from_academic_year_id' => $activeSession?->academic_year_id,
                'transferred_on' => $data['transferred_on'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'destination_school' => $data['destination_school'] ?? null,
                'status' => 'issued',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->notifyParents($student, 'Transfer', $student->full_name.' has been transferred from the school.');
            $this->log($student, 'transferred', 'Student transferred out of school');

            return $transfer->load(['student', 'fromClassSection.schoolClass', 'fromClassSection.section']);
        });
    }

    public function issueTc(Student $student, array $data): StudentTransfer
    {
        return DB::transaction(function () use ($student, $data): StudentTransfer {
            $this->assertSchoolContext();

            $activeSession = $student->sessions()->where('status', 'active')->latest()->first();

            if ($activeSession) {
                $activeSession->update([
                    'status' => 'transferred',
                    'left_on' => $data['transferred_on'] ?? now()->toDateString(),
                ]);
            }

            $student->update([
                'status' => 'transferred',
                'updated_by' => auth()->id(),
            ]);

            $transfer = StudentTransfer::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'transfer_type' => 'tc',
                'from_class_section_id' => $activeSession?->class_section_id,
                'from_academic_year_id' => $activeSession?->academic_year_id,
                'transferred_on' => $data['transferred_on'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'tc_no' => $data['tc_no'] ?? $this->generateTcNo(),
                'tc_issued_on' => $data['tc_issued_on'] ?? now()->toDateString(),
                'conduct' => $data['conduct'] ?? null,
                'destination_school' => $data['destination_school'] ?? null,
                'status' => 'issued',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->notifyParents($student, 'Transfer Certificate', 'Transfer certificate issued for '.$student->full_name.'. TC No: '.$transfer->tc_no);
            $this->log($student, 'tc_issued', 'Transfer certificate issued for student');

            return $transfer->load(['student', 'fromClassSection.schoolClass', 'fromClassSection.section']);
        });
    }

    public function markAlumni(Student $student): void
    {
        DB::transaction(function () use ($student): void {
            $this->assertSchoolContext();

            if ($student->status === 'alumni') {
                throw new RuntimeException($student->full_name.' is already marked as alumni.');
            }

            $activeSession = $student->sessions()->where('status', 'active')->latest()->first();

            if ($activeSession) {
                $activeSession->update([
                    'status' => 'alumni',
                    'left_on' => now()->toDateString(),
                ]);
            }

            $student->update([
                'status' => 'alumni',
                'updated_by' => auth()->id(),
            ]);

            StudentTransfer::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'transfer_type' => 'alumni',
                'from_class_section_id' => $activeSession?->class_section_id,
                'from_academic_year_id' => $activeSession?->academic_year_id,
                'transferred_on' => now()->toDateString(),
                'status' => 'issued',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->log($student, 'marked_alumni', 'Student marked as alumni');
        });
    }

    private function generateTcNo(): string
    {
        $schoolId = $this->schoolContext->id();
        $year = now()->year;

        $next = (int) StudentTransfer::query()
            ->withTrashed()
            ->where('school_id', $schoolId)
            ->where('transfer_type', 'tc')
            ->whereYear('created_at', now()->year)
            ->count();

        do {
            $next++;
            $candidate = sprintf('TC-%s-%04d', $year, $next);
        } while (StudentTransfer::query()->where('school_id', $schoolId)->where('tc_no', $candidate)->exists());

        return $candidate;
    }

    private function notifyParents(Student $student, string $title, string $message): void
    {
        $recipientIds = [];

        foreach ($student->guardians as $guardian) {
            if (! empty($guardian->user_id)) {
                $recipientIds[$guardian->user_id] = true;
            }
        }

        if ($recipientIds === []) {
            return;
        }

        $notification = Notification::query()->create([
            'school_id' => $student->school_id,
            'title' => $title.' - '.$student->full_name,
            'message' => $message,
            'type' => 'announcement',
            'priority' => 'high',
            'status' => 'sent',
            'target_type' => 'parents',
            'channel' => 'in_app',
            'sent_at' => now(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach (array_keys($recipientIds) as $userId) {
            $notification->users()->attach($userId, [
                'is_read' => false,
                'delivery_status' => 'delivered',
            ]);
        }
    }

    private function log(Student $student, string $event, string $description): void
    {
        activity()->causedBy(auth()->user())->performedOn($student)->event($event)->log($description);
    }

    private function assertSchoolContext(): void
    {
        if (! $this->schoolContext->id()) {
            throw new RuntimeException('School context is required to perform this action.');
        }
    }
}
