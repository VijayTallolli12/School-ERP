<?php

namespace App\Modules\Parents\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Homework\Models\Homework;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class ParentService
{
    public function __construct(
        private readonly ParentRepositoryInterface $repository,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function createParent(array $data): Guardian
    {
        return DB::transaction(function () use ($data) {
            $schoolId = $this->schoolContext->id();

            // Create user account if email provided
            $user = null;
            if (!empty($data['email'])) {
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'), // Default password
                    'school_id' => $schoolId,
                ]);

                app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
                $user->assignRole('Parent');
                $data['user_id'] = $user->id;
            }

            $data['school_id'] = $this->schoolContext->id();
            $data['created_by'] = auth()->id();

            $parent = $this->repository->create($data);

            // Attach students if provided
            if (!empty($data['student_ids'])) {
                $this->attachStudents($parent, $data['student_ids'], $data['relationships'] ?? []);
            }

            return $parent;
        });
    }

    public function updateParent(Guardian $parent, array $data): Guardian
    {
        return DB::transaction(function () use ($parent, $data) {
            $data['updated_by'] = auth()->id();

            $parent = $this->repository->update($parent, $data);

            // Update student relationships if provided
            if (isset($data['student_ids'])) {
                $this->syncStudents($parent, $data['student_ids'], $data['relationships'] ?? []);
            }

            return $parent;
        });
    }

    public function deleteParent(Guardian $parent): bool
    {
        return DB::transaction(function () use ($parent) {
            // Detach all students
            $parent->students()->detach();

            return $this->repository->delete($parent);
        });
    }

    public function attachStudents(Guardian $parent, array $studentIds, array $relationships = []): void
    {
        $syncData = [];
        foreach ($studentIds as $index => $studentId) {
            $syncData[$studentId] = [
                'relationship' => $relationships[$index] ?? 'guardian',
                'is_primary' => $index === 0, // First student is primary
            ];
        }

        $parent->students()->sync($syncData, false);
    }

    public function syncStudents(Guardian $parent, array $studentIds, array $relationships = []): void
    {
        $syncData = [];
        foreach ($studentIds as $index => $studentId) {
            $syncData[$studentId] = [
                'relationship' => $relationships[$index] ?? 'guardian',
                'is_primary' => $index === 0,
            ];
        }

        $parent->students()->sync($syncData);
    }

    public function getParentDashboardData(Guardian $parent, ?string $childUuid = null): array
    {
        $students = $parent->students()->with(['sessions' => function ($query) {
            $query->with('classSection.schoolClass', 'classSection.section')
                ->where('status', 'active');
        }])->get();

        // If a specific child is requested, filter to that child only
        if ($childUuid) {
            $students = $students->where('uuid', $childUuid);
        }

        $data = [
            'students' => $students,
            'attendance_summary' => $this->getAttendanceSummary($students),
            'fees_summary' => $this->getFeesSummary($students),
            'exam_results_summary' => $this->getExamResultsSummary($students),
            'homework_summary' => $this->getHomeworkSummary($students),
            'notifications' => $parent->notifications()->latest()->take(5)->get(),
        ];

        return $data;
    }

    private function getAttendanceSummary($students): array
    {
        $studentIds = $students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return ['present' => 0, 'absent' => 0, 'total' => 0, 'percentage' => 0];
        }

        $activeYear = $this->activeAcademicYear();

        $query = Attendance::whereIn('student_id', $studentIds);

        // Use date range from active academic year instead of academic_year_id
        // to match records that may lack the academic_year_id FK
        if ($activeYear) {
            $query->whereBetween('attendance_date', [$activeYear->starts_on, $activeYear->ends_on]);
        }

        // Use SQL aggregation instead of loading all records
        $stats = (clone $query)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status IN (\'present\', \'late\', \'half_day\') THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent
        ')->first();

        $total = (int) ($stats->total ?? 0);
        $present = (int) ($stats->present ?? 0);
        $absent = (int) ($stats->absent ?? 0);
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        return [
            'present' => $present,
            'absent' => $absent,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    private function getFeesSummary($students): array
    {
        $studentIds = $students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return ['total' => 0, 'paid' => 0, 'pending' => 0];
        }

        $activeYear = $this->activeAcademicYear();

        // Eager-load with withSum to prevent N+1 on paid_amount accessor
        $query = StudentFee::with(['items' => function ($q) {
            $q->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment')], 'amount');
        }])->whereIn('student_id', $studentIds);

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        $studentFees = $query->get();

        $total = 0;
        $paid = 0;
        $pending = 0;

        foreach ($studentFees as $fee) {
            foreach ($fee->items as $item) {
                $total += (float) $item->amount;
                $itemPaid = (float) ($item->paid_sum ?? 0);
                $paid += $itemPaid;
                $pending += max(0, (float) $item->amount - $itemPaid);
            }
        }

        return [
            'total' => $total,
            'paid' => $paid,
            'pending' => $pending,
        ];
    }

    private function getExamResultsSummary($students): array
    {
        $studentIds = $students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return ['average' => 0, 'subjects' => 0, 'total_marks' => 0, 'obtained_marks' => 0];
        }

        $activeYearId = $this->activeAcademicYearId();

        // Use SQL aggregation for exam results summary
        $aggregate = ExamResult::join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->whereIn('exam_results.student_id', $studentIds)
            ->where('exams.academic_year_id', $activeYearId)
            ->where('exams.is_published', true)
            ->selectRaw('
                SUM(exams.maximum_marks) as total_maximum_marks,
                SUM(exam_results.marks_obtained) as total_obtained_marks
            ')
            ->first();

        $totalMarks = (float) ($aggregate->total_maximum_marks ?? 0);
        $obtainedMarks = (float) ($aggregate->total_obtained_marks ?? 0);
        $average = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        // Count actual subjects assigned to the student's class section
        $subjects = 0;
        foreach ($students as $student) {
            $currentSession = $student->sessions->first();
            if (!$currentSession) {
                Log::info('[Dashboard] No active session for student', ['student_id' => $student->id]);
                continue;
            }

            $classSectionId = $currentSession->class_section_id;
            $classSection = $currentSession->classSection;
            $classId = $classSection?->class_id;

            // Try via ClassSubject (class-level assignments)
            if ($classId && $activeYearId) {
                $count = ClassSubject::where('class_id', $classId)
                    ->where('academic_year_id', $activeYearId)
                    ->where('status', 'active')
                    ->count();

                if ($count > 0) {
                    $subjects = max($subjects, $count);
                    continue;
                }
            }

            // Fallback: count distinct subjects from timetable slots (section-level)
            if ($classSectionId && $activeYearId) {
                $count = TimetableSlot::where('class_section_id', $classSectionId)
                    ->where('academic_year_id', $activeYearId)
                    ->where('status', 'active')
                    ->distinct('subject_id')
                    ->count('subject_id');

                if ($count > 0) {
                    $subjects = max($subjects, $count);
                    continue;
                }
            }
        }

        return [
            'average' => $average,
            'subjects' => $subjects,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
        ];
    }

    public function getHomeworkForStudents($students): \Illuminate\Database\Eloquent\Collection
    {
        $studentIds = $students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return collect();
        }

        $classSectionIds = StudentSession::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->pluck('class_section_id')
            ->unique()
            ->toArray();

        if (empty($classSectionIds)) {
            return collect();
        }

        $activeYearId = $this->activeAcademicYearId();

        return Homework::with('subject')
            ->whereIn('class_section_id', $classSectionIds)
            ->when($activeYearId, fn ($q) => $q->where('academic_year_id', $activeYearId))
            ->active()
            ->orderByDesc('due_date')
            ->get();
    }

    private function getHomeworkSummary($students): array
    {
        $homework = $this->getHomeworkForStudents($students);

        $total = $homework->count();
        $active = $homework->where('status', 'active')->count();
        $overdue = $homework->filter(fn (Homework $hw) => $hw->due_date?->isPast())->count();

        return [
            'total' => $total,
            'active' => $active,
            'overdue' => $overdue,
            'recent' => $homework->take(3),
        ];
    }

    private function activeAcademicYear(): ?AcademicYear
    {
        return AcademicYear::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active')
            ->first();
    }

    private function activeAcademicYearId(): ?int
    {
        return $this->activeAcademicYear()?->id;
    }
}
