<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FeeQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
        private readonly FeeService $feeService,
    ) {}

    public function totalOutstanding(): string
    {
        $schoolId = $this->schoolContext->id();

        $pending = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn($q) => $q->where('school_id', $schoolId))
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_sum FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id) GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->selectRaw('COALESCE(SUM(student_fee_items.amount - COALESCE(fpi.paid_sum, 0)), 0) as total_pending')
            ->value('total_pending');

        $totalDue = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn($q) => $q->where('school_id', $schoolId))
            ->sum('amount');

        return "Total outstanding fees: \u{20B9}" . number_format(max(0, $pending), 2) . " (out of \u{20B9}" . number_format($totalDue, 2) . " total assigned)";
    }

    public function outstanding(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $pending = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn($q) => $q->where('school_id', $schoolId))
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_sum FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id) GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->selectRaw('COALESCE(SUM(student_fee_items.amount - COALESCE(fpi.paid_sum, 0)), 0) as total_pending')
            ->value('total_pending');

        $totalDue = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn($q) => $q->where('school_id', $schoolId))
            ->sum('amount');

        $pending = max(0, $pending);

        return [
            'count' => 1,
            'records' => [],
            'summary' => [
                'total_outstanding' => round($pending, 2),
                'total_assigned' => round($totalDue, 2),
                'currency' => 'INR',
            ],
        ];
    }

    public function pending(array $parameters): array
    {
        $items = $this->feeService->pendingFeeItemsQuery()
            ->with(['studentFee.student', 'feeCategory'])
            ->get();

        $studentBalances = $this->buildBalances($items);
        $studentBalances = $this->applyScopeToBalances($studentBalances, $parameters);

        $limit = max(1, min((int) ($parameters['limit'] ?? 30), 50));

        return [
            'count' => count($studentBalances),
            'records' => array_slice(array_values($studentBalances), 0, $limit),
            'summary' => [
                'total_students' => count($studentBalances),
                'total_outstanding' => round(array_sum(array_column($studentBalances, 'balance')), 2),
                'currency' => 'INR',
            ],
        ];
    }

    public function pendingAbove(array $parameters): array
    {
        $threshold = (float) ($parameters['amount'] ?? 1000);

        $items = $this->feeService->pendingFeeItemsQuery()
            ->with(['studentFee.student', 'feeCategory'])
            ->get();

        $studentBalances = $this->buildBalances($items);
        $studentBalances = $this->applyScopeToBalances($studentBalances, $parameters);
        $filtered = array_values(array_filter($studentBalances, fn ($s) => $s['balance'] >= $threshold));
        usort($filtered, fn ($a, $b) => $b['balance'] <=> $a['balance']);

        $limit = max(1, min((int) ($parameters['limit'] ?? 30), 50));

        return [
            'count' => count($filtered),
            'records' => array_slice($filtered, 0, $limit),
            'summary' => [
                'threshold' => $threshold,
                'currency' => 'INR',
            ],
        ];
    }

    public function todayCollection(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();
        $today = Carbon::today()->toDateString();

        $total = (float) FeePayment::query()
            ->where('school_id', $schoolId)
            ->whereDate('paid_on', $today)
            ->sum('amount');

        $count = FeePayment::query()
            ->where('school_id', $schoolId)
            ->whereDate('paid_on', $today)
            ->count();

        return [
            'count' => $count,
            'records' => [],
            'summary' => [
                'date' => $today,
                'total_collected' => round($total, 2),
                'payments' => $count,
                'currency' => 'INR',
            ],
        ];
    }

    public function topDefaulters(array $parameters): array
    {
        $items = $this->feeService->pendingFeeItemsQuery()
            ->with(['studentFee.student'])
            ->get();

        $studentBalances = $this->buildBalances($items);
        $studentBalances = $this->applyScopeToBalances($studentBalances, $parameters);
        usort($studentBalances, fn ($a, $b) => $b['balance'] <=> $a['balance']);

        $limit = max(1, min((int) ($parameters['limit'] ?? 10), 50));

        return [
            'count' => count($studentBalances),
            'records' => array_slice(array_values($studentBalances), 0, $limit),
            'summary' => null,
        ];
    }

    private function buildBalances($items): array
    {
        $balances = [];
        foreach ($items as $item) {
            $balance = max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0));
            $studentId = $item->studentFee->student_id;
            if (!isset($balances[$studentId])) {
                $student = $item->studentFee->student;
                $balances[$studentId] = [
                    'student_id' => $studentId,
                    'name' => $student->full_name ?? "Student #{$studentId}",
                    'admission_no' => $student->admission_no ?? '-',
                    'class_section_id' => $student->currentSession->first()?->class_section_id,
                    'balance' => 0,
                ];
            }
            $balances[$studentId]['balance'] += $balance;
        }

        foreach ($balances as &$entry) {
            $entry['balance'] = round($entry['balance'], 2);
        }

        return $balances;
    }

    private function applyScopeToBalances(array $balances, array $parameters): array
    {
        if (!empty($parameters['student_ids'])) {
            $allowed = array_map('intval', (array) $parameters['student_ids']);
            $balances = array_filter(
                $balances,
                fn ($s) => in_array((int) ($s['student_id'] ?? 0), $allowed, true)
            );
        }

        if (!empty($parameters['student_id'])) {
            $studentId = (int) $parameters['student_id'];
            $balances = array_filter(
                $balances,
                fn ($s) => (int) ($s['student_id'] ?? 0) === $studentId
            );
        }

        if (!empty($parameters['class_section_id'])) {
            $classSectionId = (int) $parameters['class_section_id'];
            $balances = array_filter(
                $balances,
                fn ($s) => ($s['class_section_id'] ?? null) === $classSectionId
            );
        } elseif (!empty($parameters['class_section_ids'])) {
            $allowedClasses = array_map('intval', (array) $parameters['class_section_ids']);
            $balances = array_filter(
                $balances,
                fn ($s) => in_array((int) ($s['class_section_id'] ?? 0), $allowedClasses, true)
            );
        }

        return $balances;
    }

    public function studentsWithPendingAbove(string $amount = '1000'): string
    {
        $threshold = (float) $amount;

        $items = $this->feeService->pendingFeeItemsQuery()
            ->with(['studentFee.student', 'feeCategory'])
            ->get();

        $studentBalances = [];
        foreach ($items as $item) {
            $balance = max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0));
            $studentId = $item->studentFee->student_id;
            if (!isset($studentBalances[$studentId])) {
                $studentBalances[$studentId] = [
                    'name' => $item->studentFee->student->full_name ?? "Student #{$studentId}",
                    'admission_no' => $item->studentFee->student->admission_no ?? '-',
                    'balance' => 0,
                ];
            }
            $studentBalances[$studentId]['balance'] += $balance;
        }

        $filtered = array_filter($studentBalances, fn($s) => $s['balance'] >= $threshold);
        usort($filtered, fn($a, $b) => $b['balance'] <=> $a['balance']);

        if (empty($filtered)) {
            return "No students with pending fees above \u{20B9}" . number_format($threshold, 2) . ".";
        }

        $lines = [];
        foreach ($filtered as $s) {
            $lines[] = "{$s['name']} ({$s['admission_no']}) - \u{20B9}" . number_format($s['balance'], 2);
        }

        return "Students with pending fees above \u{20B9}" . number_format($threshold, 2) . " (" . count($filtered) . " found):\n" . implode("\n", array_slice($lines, 0, 30));
    }

    public function todayCollectionString(): string
    {
        $schoolId = $this->schoolContext->id();
        $today = Carbon::today()->toDateString();

        $total = (float) FeePayment::query()
            ->where('school_id', $schoolId)
            ->whereDate('paid_on', $today)
            ->sum('amount');

        $count = FeePayment::query()
            ->where('school_id', $schoolId)
            ->whereDate('paid_on', $today)
            ->count();

        return "Today's collection ({$today}): \u{20B9}" . number_format($total, 2) . " from {$count} payment(s)";
    }
}
