<?php

namespace App\Modules\Reports\Repositories;

use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Students\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeeDefaulterReportRepository implements FeeDefaulterReportRepositoryInterface
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function defaulters(
        ?int $academicYearId,
        ?int $classSectionId,
        ?int $studentId,
        ?int $feeStructureId,
        ?string $fromDueDate,
        ?string $toDueDate,
        ?float $minOutstanding,
        ?float $maxOutstanding
    ): array {
        $schoolId = $this->schoolContext->id();

        $items = StudentFeeItem::query()
            ->with([
                'feeCategory',
                'studentFee.student.guardians',
                'studentFee.student.sessions' => fn($q) => $q->where('status', 'active'),
                'studentFee.student.sessions.classSection.schoolClass',
                'studentFee.student.sessions.classSection.section',
                'studentFee.feeStructure',
            ])
            ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
            ->whereHas('studentFee', function ($q) use ($schoolId, $academicYearId, $studentId, $feeStructureId) {
                $q->where('school_id', $schoolId);
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
                if ($studentId) $q->where('student_id', $studentId);
                if ($feeStructureId) $q->where('fee_structure_id', $feeStructureId);
            });

        if ($classSectionId) {
            $items->whereHas('studentFee.student.sessions', function ($q) use ($classSectionId) {
                $q->where('class_section_id', $classSectionId)->where('status', 'active');
            });
        }

        if ($fromDueDate) {
            $items->where('due_date', '>=', Carbon::parse($fromDueDate));
        }
        if ($toDueDate) {
            $items->where('due_date', '<=', Carbon::parse($toDueDate));
        }

        $rows = $items->orderBy('due_date')->limit(50000)->get();
        $today = Carbon::today();

        $studentGroups = $rows->groupBy(fn($item) => $item->studentFee->student_id);
        $defaulters = [];
        $totalAssigned = 0;
        $totalCollected = 0;
        $studentOutstandings = [];

        foreach ($studentGroups as $studentIdKey => $studentItems) {
            $firstItem = $studentItems->first();
            $student = $firstItem->studentFee?->student;
            if (!$student) continue;

            $session = $student->sessions->first();
            $classLabel = $session && $session->classSection
                ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name
                : 'Unassigned';

            $feeStructureName = $firstItem->studentFee?->feeStructure?->name
                ?? $firstItem->studentFee?->feeStructure?->classSection?->schoolClass?->name
                ?? 'N/A';

            $totalFee = 0;
            $totalPaid = 0;
            $earliestDueDate = null;
            $hasOverdueItem = false;

            foreach ($studentItems as $item) {
                $amount = (float) $item->amount;
                $paid = (float) ($item->paid_sum ?? 0);
                $totalFee += $amount;
                $totalPaid += $paid;

                $balance = max(0, $amount - $paid);
                if ($balance > 0.009 && $item->due_date) {
                    if (!$earliestDueDate || $item->due_date->lt($earliestDueDate)) {
                        $earliestDueDate = $item->due_date;
                    }
                    if ($item->due_date->lt($today)) {
                        $hasOverdueItem = true;
                    }
                }
            }

            $outstanding = max(0, $totalFee - $totalPaid);

            if ($minOutstanding !== null && $outstanding < $minOutstanding) continue;
            if ($maxOutstanding !== null && $outstanding > $maxOutstanding) continue;

            // Parent info
            $primaryGuardian = $student->guardians->firstWhere('is_primary', true) ?? $student->guardians->first();
            $parentName = $primaryGuardian?->name ?? 'N/A';
            $parentMobile = $primaryGuardian?->phone ?? 'N/A';

            $dueDateStr = $earliestDueDate?->format('Y-m-d') ?? 'N/A';
            $daysOverdue = $earliestDueDate && $earliestDueDate->lt($today)
                ? (int) $earliestDueDate->diffInDays($today)
                : 0;

            if ($outstanding <= 0.009) {
                $status = 'Paid';
            } elseif ($hasOverdueItem) {
                $status = 'Overdue';
            } else {
                $status = 'Pending';
            }

            $totalAssigned += $totalFee;
            $totalCollected += $totalPaid;
            $studentOutstandings[] = $outstanding;

            $defaulters[] = [
                'student_id' => $student->id,
                'student_name' => $student->full_name ?? 'N/A',
                'admission_no' => $student->admission_no ?? '',
                'class_section' => $classLabel,
                'parent_name' => $parentName,
                'parent_mobile' => $parentMobile,
                'parent_id' => $primaryGuardian?->id ?? null,
                'fee_structure' => $feeStructureName,
                'total_fee' => round($totalFee, 2),
                'amount_paid' => round($totalPaid, 2),
                'outstanding' => round($outstanding, 2),
                'due_date' => $dueDateStr,
                'days_overdue' => $daysOverdue,
                'status' => $status,
            ];
        }

        $totalOutstanding = round($totalAssigned - $totalCollected, 2);
        $studentsWithDues = count(array_filter($defaulters, fn($d) => $d['status'] !== 'Paid'));
        $overdueStudents = count(array_filter($defaulters, fn($d) => $d['status'] === 'Overdue'));
        $highestOutstanding = !empty($studentOutstandings) ? round(max($studentOutstandings), 2) : 0;
        $averageOutstanding = !empty($studentOutstandings) ? round(array_sum($studentOutstandings) / count($studentOutstandings), 2) : 0;
        $collectionPct = $totalAssigned > 0 ? round(($totalCollected / $totalAssigned) * 100, 2) : 0;

        $summary = [
            'total_assigned' => round($totalAssigned, 2),
            'total_collected' => round($totalCollected, 2),
            'total_outstanding' => $totalOutstanding,
            'collection_percentage' => $collectionPct,
            'students_with_dues' => $studentsWithDues,
            'overdue_students' => $overdueStudents,
            'highest_outstanding' => $highestOutstanding,
            'average_outstanding' => $averageOutstanding,
        ];

        // Chart: Outstanding by class
        $classGroups = collect($defaulters)->groupBy('class_section');
        $outstandingByClass = $classGroups->map(fn($group, $label) => [
            'label' => $label,
            'value' => round($group->sum('outstanding'), 2),
        ])->values()->toArray();

        // Chart: Collection vs Outstanding
        $collectionVsOutstanding = [
            'collected' => round($totalCollected, 2),
            'outstanding' => $totalOutstanding,
        ];

        // Chart: Monthly collection trend
        $monthlyTrend = FeePayment::query()
            ->where('school_id', $schoolId)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->select(
                DB::raw("DATE_FORMAT(paid_on, '%Y-%m') as month"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'month' => $row->month,
                'label' => Carbon::parse($row->month . '-01')->format('M Y'),
                'value' => round((float) $row->total, 2),
            ])
            ->values()
            ->toArray();

        $chartData = [
            'outstanding_by_class' => $outstandingByClass,
            'collection_vs_outstanding' => $collectionVsOutstanding,
            'monthly_trend' => $monthlyTrend,
        ];

        return compact('summary', 'defaulters', 'chartData');
    }

    public function getStudentsByClass(?int $classSectionId): Collection
    {
        $schoolId = $this->schoolContext->id();
        return Student::with(['sessions' => fn($q) => $q->where('status', 'active')])
            ->whereHas('sessions', function ($q) use ($classSectionId, $schoolId) {
                $q->where('class_section_id', $classSectionId)
                  ->where('status', 'active')
                  ->whereHas('classSection', fn($csq) => $csq->whereHas('schoolClass', fn($sq) => $sq->where('school_id', $schoolId)));
            })
            ->where('school_id', $schoolId)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_no']);
    }
}
