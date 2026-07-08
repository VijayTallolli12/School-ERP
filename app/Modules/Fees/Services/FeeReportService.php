<?php

namespace App\Modules\Fees\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeePaymentItem;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Models\StudentFeeItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeReportService
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function dashboardStats(): array
    {
        $schoolId = $this->schoolContext->id();

        $totalCollected = FeePayment::where('school_id', $schoolId)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('amount') ?? 0;

        $monthlyCollection = FeePayment::where('school_id', $schoolId)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->whereYear('paid_on', now()->year)
            ->whereMonth('paid_on', now()->month)
            ->sum('amount') ?? 0;

        $pendingFees = StudentFeeItem::whereHas('studentFee', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
            ->select(DB::raw('SUM(amount - COALESCE((SELECT SUM(amount) FROM fee_payment_items WHERE student_fee_item_id = student_fee_items.id), 0)) as balance'))
            ->value('balance') ?? 0;

        return [
            'total_collected' => round((float) $totalCollected, 2),
            'monthly_collection' => round((float) $monthlyCollection, 2),
            'pending_fees' => round(max(0, (float) $pendingFees), 2),
        ];
    }

    public function collectionSummary(?int $academicYearId = null): array
    {
        $schoolId = $this->schoolContext->id();

        $items = StudentFeeItem::with([
            'studentFee.student.sessions.classSection.schoolClass',
            'studentFee.student.sessions.classSection.section',
        ])
            ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
            ->whereHas('studentFee', function ($q) use ($schoolId, $academicYearId) {
                $q->where('school_id', $schoolId);
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
            })
            ->get();

        $groups = $items->groupBy(fn($item) => optional($item->studentFee?->student?->sessions?->first()?->classSection)->id ?? 'unassigned');

        $summary = [];
        foreach ($groups as $csId => $csItems) {
            $first = $csItems->first();
            $cs = $first->studentFee?->student?->sessions?->first()?->classSection;
            $label = $cs ? $cs->schoolClass->name . ' - ' . $cs->section->name : 'Unassigned';

            $totalDue = $csItems->sum('amount');
            $totalPaid = $csItems->sum(fn($i) => (float) ($i->paid_sum ?? 0));
            $balance = round(max(0, $totalDue - $totalPaid), 2);

            $summary[] = [
                'class_section' => $label,
                'total_due' => round((float) $totalDue, 2),
                'total_paid' => round((float) $totalPaid, 2),
                'balance' => $balance,
            ];
        }

        return $summary;
    }

    public function pendingFees(?int $academicYearId = null): array
    {
        $schoolId = $this->schoolContext->id();

        $items = StudentFeeItem::with([
            'feeCategory',
            'studentFee.student',
            'studentFee.academicYear',
            'studentFee.student.sessions.classSection.schoolClass',
            'studentFee.student.sessions.classSection.section',
        ])
            ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
            ->whereHas('studentFee', function ($q) use ($schoolId, $academicYearId) {
                $q->where('school_id', $schoolId);
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
            })
            ->get()
            ->filter(fn($item) => (float) ($item->paid_sum ?? 0) < (float) $item->amount);

        return $items->map(fn($item) => [
            'student' => $item->studentFee?->student?->full_name ?? 'N/A',
            'admission_no' => $item->studentFee?->student?->admission_no ?? '',
            'academic_year' => $item->studentFee?->academicYear?->name ?? '',
            'category' => $item->feeCategory?->name ?? 'N/A',
            'amount' => round((float) $item->amount, 2),
            'paid' => round((float) ($item->paid_sum ?? 0), 2),
            'balance' => round(max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0)), 2),
            'due_date' => $item->due_date?->format('Y-m-d') ?? 'N/A',
            'overdue' => $item->due_date && $item->due_date->isPast() ? 'Yes' : 'No',
        ])->values()->toArray();
    }

    public function overdueFees(?int $academicYearId = null): array
    {
        $schoolId = $this->schoolContext->id();

        $items = StudentFeeItem::with([
            'feeCategory',
            'studentFee.student',
            'studentFee.academicYear',
            'studentFee.student.sessions.classSection.schoolClass',
            'studentFee.student.sessions.classSection.section',
        ])
            ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
            ->whereHas('studentFee', function ($q) use ($schoolId, $academicYearId) {
                $q->where('school_id', $schoolId);
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
            })
            ->where('due_date', '<', now())
            ->get()
            ->filter(fn($item) => (float) ($item->paid_sum ?? 0) < (float) $item->amount);

        return $items->map(fn($item) => [
            'student' => $item->studentFee?->student?->full_name ?? 'N/A',
            'admission_no' => $item->studentFee?->student?->admission_no ?? '',
            'academic_year' => $item->studentFee?->academicYear?->name ?? '',
            'category' => $item->feeCategory?->name ?? 'N/A',
            'amount' => round((float) $item->amount, 2),
            'paid' => round((float) ($item->paid_sum ?? 0), 2),
            'balance' => round(max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0)), 2),
            'due_date' => $item->due_date?->format('Y-m-d') ?? 'N/A',
        ])->values()->toArray();
    }

    public function defaultersList(?int $academicYearId = null, ?int $classSectionId = null, ?int $feeStructureId = null, ?float $minOutstanding = null, ?float $maxOutstanding = null): array
    {
        $schoolId = $this->schoolContext->id();

        $items = StudentFeeItem::with([
            'feeCategory',
            'studentFee.student.guardians',
            'studentFee.student.sessions' => fn($q) => $q->where('status', 'active'),
            'studentFee.student.sessions.classSection.schoolClass',
            'studentFee.student.sessions.classSection.section',
            'studentFee.feeStructure',
        ])
            ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
            ->whereHas('studentFee', function ($q) use ($schoolId, $academicYearId, $feeStructureId) {
                $q->where('school_id', $schoolId);
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
                if ($feeStructureId) $q->where('fee_structure_id', $feeStructureId);
            });

        if ($classSectionId) {
            $items->whereHas('studentFee.student.sessions', function ($q) use ($classSectionId) {
                $q->where('class_section_id', $classSectionId)->where('status', 'active');
            });
        }

        $rows = $items->orderBy('due_date')->limit(50000)->get();
        $today = Carbon::today();

        $studentGroups = $rows->groupBy(fn($item) => $item->studentFee->student_id);

        $defaulters = [];
        foreach ($studentGroups as $studentIdKey => $studentItems) {
            $firstItem = $studentItems->first();
            $student = $firstItem->studentFee?->student;
            if (!$student) continue;

            $session = $student->sessions->first();
            $classLabel = $session && $session->classSection
                ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name
                : 'Unassigned';

            $totalFee = 0;
            $totalPaid = 0;

            foreach ($studentItems as $item) {
                $totalFee += (float) $item->amount;
                $totalPaid += (float) ($item->paid_sum ?? 0);
            }

            $outstanding = round(max(0, $totalFee - $totalPaid), 2);

            if ($minOutstanding !== null && $outstanding < $minOutstanding) continue;
            if ($maxOutstanding !== null && $outstanding > $maxOutstanding) continue;

            $primaryGuardian = $student->guardians->firstWhere('is_primary', true) ?? $student->guardians->first();
            $parentName = $primaryGuardian?->name ?? 'N/A';
            $parentMobile = $primaryGuardian?->phone ?? 'N/A';

            $defaulters[] = [
                'student_id' => $student->id,
                'student_name' => $student->full_name ?? 'N/A',
                'admission_no' => $student->admission_no ?? '',
                'class_section' => $classLabel,
                'parent_name' => $parentName,
                'parent_mobile' => $parentMobile,
                'total_fee' => round($totalFee, 2),
                'amount_paid' => round($totalPaid, 2),
                'outstanding' => $outstanding,
                'status' => $outstanding > 0 ? 'Pending' : 'Paid',
            ];
        }

        return $defaulters;
    }
}
