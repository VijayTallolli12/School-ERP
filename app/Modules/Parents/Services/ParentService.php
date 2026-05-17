<?php

namespace App\Modules\Parents\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentService
{
    public function __construct(
        private readonly ParentRepositoryInterface $repository,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function createParent(array $data): Guardian
    {
        return DB::transaction(function () use ($data) {
            // Create user account if email provided
            $user = null;
            if (!empty($data['email'])) {
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'), // Default password
                    'school_id' => $this->schoolContext->getSchoolId(),
                ]);

                $user->assignRole('Parent');
                $data['user_id'] = $user->id;
            }

            $data['school_id'] = $this->schoolContext->getSchoolId();
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

    public function getParentDashboardData(Guardian $parent): array
    {
        $students = $parent->students()->with(['sessions' => function ($query) {
            $query->with('classSection.schoolClass', 'classSection.section')
                ->where('status', 'active');
        }])->get();

        $data = [
            'students' => $students,
            'attendance_summary' => $this->getAttendanceSummary($students),
            'fees_summary' => $this->getFeesSummary($students),
            'exam_results_summary' => $this->getExamResultsSummary($students),
            'notifications' => $parent->notifications()->latest()->take(5)->get(),
        ];

        return $data;
    }

    private function getAttendanceSummary($students): array
    {
        $studentIds = $students->pluck('id')->toArray();
        
        $attendanceRecords = \App\Modules\Attendance\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $this->schoolContext->getAcademicYearId())
            ->get();

        $total = $attendanceRecords->count();
        $present = $attendanceRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
        $absent = $attendanceRecords->where('status', 'absent')->count();
        
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
        
        $studentFees = \App\Modules\Fees\Models\StudentFee::with('items')
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $this->schoolContext->getAcademicYearId())
            ->get();

        $total = 0;
        $paid = 0;
        $pending = 0;

        foreach ($studentFees as $fee) {
            foreach ($fee->items as $item) {
                $total += $item->amount;
                $paid += $item->paid_amount;
                $pending += $item->balance;
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
        
        $results = \App\Modules\Exams\Models\ExamResult::with('exam')
            ->whereIn('student_id', $studentIds)
            ->whereHas('exam', function ($query) {
                $query->where('academic_year_id', $this->schoolContext->getAcademicYearId())
                      ->where('is_published', true);
            })
            ->get();

        $totalMarks = 0;
        $obtainedMarks = 0;
        $subjects = $results->count();

        foreach ($results as $result) {
            $totalMarks += $result->exam->maximum_marks;
            $obtainedMarks += $result->marks_obtained;
        }

        $average = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        return [
            'average' => $average,
            'subjects' => $subjects,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
        ];
    }
}