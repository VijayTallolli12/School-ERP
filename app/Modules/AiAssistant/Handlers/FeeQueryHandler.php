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

    public function todayCollection(): string
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

    public function topDefaulters(): string
    {
        $items = $this->feeService->pendingFeeItemsQuery()
            ->with(['studentFee.student'])
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

        usort($studentBalances, fn($a, $b) => $b['balance'] <=> $a['balance']);
        $top = array_slice($studentBalances, 0, 10);

        if (empty($top)) {
            return 'No fee defaulters found.';
        }

        $lines = [];
        foreach ($top as $i => $s) {
            $lines[] = ($i + 1) . ". {$s['name']} ({$s['admission_no']}) - \u{20B9}" . number_format($s['balance'], 2);
        }

        return "Top fee defaulters:\n" . implode("\n", $lines);
    }
}
