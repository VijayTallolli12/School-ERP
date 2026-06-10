<?php

namespace App\Modules\Fees\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeePaymentItem;
use App\Modules\Fees\Models\FeeReceiptSequence;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FeeService
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function createFeeCategory(array $data): FeeCategory
    {
        return FeeCategory::query()->create($data);
    }

    public function updateFeeCategory(FeeCategory $category, array $data): FeeCategory
    {
        $category->fill($data)->save();

        return $category->refresh();
    }

    public function deleteFeeCategory(FeeCategory $category): void
    {
        if ($category->structureItems()->exists() || $category->studentFeeItems()->exists()) {
            throw new RuntimeException('This fee category is in use and cannot be deleted.');
        }

        $category->delete();
    }

    public function createFeeStructure(array $data): FeeStructure
    {
        return DB::transaction(function () use ($data): FeeStructure {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $structure = FeeStructure::query()->create($data);
            $this->syncStructureItems($structure, $items);

            return $structure->load(['items.feeCategory', 'academicYear', 'classSection.schoolClass', 'classSection.section']);
        });
    }

    public function updateFeeStructure(FeeStructure $structure, array $data): FeeStructure
    {
        return DB::transaction(function () use ($structure, $data): FeeStructure {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $structure->fill($data)->save();

            if (is_array($items)) {
                $this->syncStructureItems($structure, $items);
            }

            return $structure->load(['items.feeCategory', 'academicYear', 'classSection.schoolClass', 'classSection.section']);
        });
    }

    /**
     * @param  array<int, array{fee_category_id: int|string, amount: float|string}>  $items
     */
    private function syncStructureItems(FeeStructure $structure, array $items): void
    {
        $structure->items()->delete();

        foreach (array_values($items) as $index => $row) {
            $structure->items()->create([
                'fee_category_id' => (int) $row['fee_category_id'],
                'amount' => $row['amount'],
                'sort_order' => $index,
            ]);
        }
    }

    public function deleteFeeStructure(FeeStructure $structure): void
    {
        if ($structure->studentFees()->exists()) {
            throw new RuntimeException('This fee structure is assigned to students and cannot be deleted.');
        }

        $structure->items()->delete();
        $structure->delete();
    }

    public function assignStudentFee(array $data): StudentFee
    {
        return DB::transaction(function () use ($data): StudentFee {
            $structure = FeeStructure::query()->with('items')->findOrFail($data['fee_structure_id']);

            if ((int) $structure->academic_year_id !== (int) $data['academic_year_id']) {
                throw new RuntimeException('The fee structure does not belong to the selected academic year.');
            }

            $dueDate = isset($data['default_due_date']) ? Carbon::parse($data['default_due_date']) : null;

            $existing = StudentFee::query()
                ->where('student_id', $data['student_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->first();

            if ($existing) {
                throw new RuntimeException('This student already has a fee assignment for the selected academic year.');
            }

            $studentFee = StudentFee::query()->create([
                'student_id' => $data['student_id'],
                'academic_year_id' => $data['academic_year_id'],
                'fee_structure_id' => $structure->id,
                'status' => 'active',
                'assigned_at' => now(),
            ]);

            foreach ($structure->items as $line) {
                $studentFee->items()->create([
                    'fee_category_id' => $line->fee_category_id,
                    'amount' => $line->amount,
                    'due_date' => $dueDate,
                ]);
            }

            return $studentFee->load(['items.feeCategory', 'student', 'academicYear', 'feeStructure']);
        });
    }

    /**
     * @return array{assigned: int, skipped: int}
     */
    public function bulkAssignStudentFees(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $structure = FeeStructure::query()->with('items')->findOrFail($data['fee_structure_id']);

            if ((int) $structure->academic_year_id !== (int) $data['academic_year_id']) {
                throw new RuntimeException('The selected fee structure does not belong to the chosen academic year.');
            }

            if ((int) $structure->class_section_id !== (int) $data['class_section_id']) {
                throw new RuntimeException('The selected fee structure does not match the chosen class section.');
            }

            $dueDate = isset($data['default_due_date']) ? Carbon::parse($data['default_due_date']) : null;

            $studentIds = Student::query()
                ->whereHas('sessions', function ($q) use ($data): void {
                    $q->where('academic_year_id', $data['academic_year_id'])
                        ->where('class_section_id', $data['class_section_id'])
                        ->where('status', 'active');
                })
                ->pluck('id');

            // Batch check existing assignments to avoid N+1
            $existingIds = StudentFee::query()
                ->whereIn('student_id', $studentIds)
                ->where('academic_year_id', $data['academic_year_id'])
                ->pluck('student_id')
                ->toArray();

            $existingSet = array_flip($existingIds);
            $assigned = 0;
            $skipped = 0;

            foreach ($studentIds as $studentId) {
                if (isset($existingSet[$studentId])) {
                    $skipped++;
                    continue;
                }

                $studentFee = StudentFee::query()->create([
                    'student_id' => $studentId,
                    'academic_year_id' => $data['academic_year_id'],
                    'fee_structure_id' => $structure->id,
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);

                foreach ($structure->items as $line) {
                    $studentFee->items()->create([
                        'fee_category_id' => $line->fee_category_id,
                        'amount' => $line->amount,
                        'due_date' => $dueDate,
                    ]);
                }

                $assigned++;
            }

            return ['assigned' => $assigned, 'skipped' => $skipped];
        });
    }

    public function updateStudentFee(StudentFee $studentFee, array $data): StudentFee
    {
        $studentFee->fill([
            'status' => $data['status'] ?? $studentFee->status,
        ])->save();

        if (! empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $row) {
                $item = StudentFeeItem::query()
                    ->where('student_fee_id', $studentFee->id)
                    ->whereKey($row['id'])
                    ->firstOrFail();

                if ($this->itemHasNonVoidPayments($item)) {
                    throw new RuntimeException('Fee lines with payments cannot be changed.');
                }

                $item->update([
                    'amount' => $row['amount'],
                    'due_date' => isset($row['due_date']) ? Carbon::parse($row['due_date']) : null,
                ]);
            }
        }

        return $studentFee->load(['items.feeCategory', 'student', 'academicYear', 'feeStructure']);
    }

    public function deleteStudentFee(StudentFee $studentFee): void
    {
        foreach ($studentFee->items as $item) {
            if ($this->itemHasNonVoidPayments($item)) {
                throw new RuntimeException('Cannot remove a fee assignment that already has collections.');
            }
        }

        $studentFee->items()->delete();
        $studentFee->delete();
    }

    private function itemHasNonVoidPayments(StudentFeeItem $item): bool
    {
        return FeePaymentItem::query()
            ->where('student_fee_item_id', $item->id)
            ->whereHas('feePayment')
            ->exists();
    }

    public function recordPayment(array $data): FeePayment
    {
        return DB::transaction(function () use ($data): FeePayment {
            $schoolId = $this->schoolContext->id();
            if (! $schoolId) {
                throw new RuntimeException('School context is required to record payments.');
            }

            $academicYearId = (int) $data['academic_year_id'];
            $studentId = (int) $data['student_id'];

            $lines = $data['lines'] ?? [];
            $total = 0.0;

            foreach ($lines as $line) {
                $total += (float) $line['amount'];
            }

            if ($total <= 0) {
                throw new RuntimeException('Payment amount must be greater than zero.');
            }

            $receiptNumber = $this->nextReceiptNumber($schoolId, $academicYearId);

            $payment = FeePayment::query()->create([
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'receipt_number' => $receiptNumber,
                'payment_mode' => $data['payment_mode'],
                'amount' => $total,
                'remarks' => $data['remarks'] ?? null,
                'paid_on' => Carbon::parse($data['paid_on'])->toDateString(),
                'collected_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                $item = StudentFeeItem::query()
                    ->whereHas('studentFee', fn ($q) => $q->where('student_id', $studentId)->where('academic_year_id', $academicYearId))
                    ->whereKey($line['student_fee_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $paid = (float) $item->paymentItems()->whereHas('feePayment')->sum('amount');
                $balance = max(0, (float) $item->amount - $paid);
                $pay = (float) $line['amount'];

                if ($pay <= 0 || $pay > $balance + 0.0001) {
                    throw new RuntimeException('Invalid amount for one or more fee lines.');
                }

                FeePaymentItem::query()->create([
                    'fee_payment_id' => $payment->id,
                    'student_fee_item_id' => $item->id,
                    'amount' => $pay,
                ]);
            }

            return $payment->load(['items.studentFeeItem.feeCategory', 'student', 'academicYear']);
        });
    }

    public function deleteFeePayment(FeePayment $payment): void
    {
        $payment->items()->delete();
        $payment->delete();
    }

    private function nextReceiptNumber(int $schoolId, int $academicYearId): string
    {
        FeeReceiptSequence::query()->firstOrCreate(
            [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
            ],
            ['last_number' => 0],
        );

        $row = FeeReceiptSequence::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->lockForUpdate()
            ->firstOrFail();

        $row->increment('last_number');

        return sprintf('RCP-%d-%d-%06d', $schoolId, $academicYearId, (int) $row->fresh()->last_number);
    }

    /**
     * @return EloquentCollection<int, StudentFeeItem>
     */
    public function listStudentFeeItemsForCollection(int $studentId, int $academicYearId): EloquentCollection
    {
        return StudentFeeItem::query()
            ->whereHas('studentFee', function ($q) use ($studentId, $academicYearId): void {
                $q->where('student_id', $studentId)->where('academic_year_id', $academicYearId);
            })
            ->with('feeCategory')
            ->withSum(['paymentItems as paid_sum' => fn ($q) => $q->whereHas('feePayment')], 'amount')
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function collectionReport(?string $from, ?string $to, ?int $classSectionId, ?string $paymentMode): array
    {
        $q = FeePayment::query()->with(['student', 'academicYear', 'collector']);

        if ($from) {
            $q->whereDate('paid_on', '>=', $from);
        }
        if ($to) {
            $q->whereDate('paid_on', '<=', $to);
        }
        if ($paymentMode) {
            $q->where('payment_mode', $paymentMode);
        }
        if ($classSectionId) {
            $q->whereHas('student.sessions', function ($sq) use ($classSectionId): void {
                $sq->where('class_section_id', $classSectionId)->where('status', 'active');
            });
        }

        return $q->orderByDesc('paid_on')->orderByDesc('id')->limit(5000)->get()->map(fn (FeePayment $p) => [
            'receipt_number' => $p->receipt_number,
            'paid_on' => $p->paid_on?->format('Y-m-d'),
            'student' => $p->student?->full_name,
            'admission_no' => $p->student?->admission_no,
            'amount' => (float) $p->amount,
            'payment_mode' => FeePayment::paymentModes()[$p->payment_mode] ?? $p->payment_mode,
            'collector' => $p->collector?->name,
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function dailyCollectionReport(string $date): array
    {
        return $this->collectionReport($date, $date, null, null);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function dueReport(?int $academicYearId, bool $overdueOnly): array
    {
        $q = StudentFeeItem::query()
            ->with([
                'feeCategory',
                'studentFee.student',
                'studentFee.academicYear',
            ])
            ->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment')], 'amount');

        if ($academicYearId) {
            $q->whereHas('studentFee', fn ($sq) => $sq->where('academic_year_id', $academicYearId));
        }

        // Filter items with balance at the SQL level using HAVING
        $q->havingRaw('COALESCE(paid_sum, 0) < student_fee_items.amount');

        if ($overdueOnly) {
            $q->whereDate('due_date', '<', now());
        }

        $rows = $q->orderBy('due_date')->limit(10000)->get();

        $out = [];

        foreach ($rows as $item) {
            $balance = max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0));

            if ($balance <= 0.009) {
                continue;
            }

            $isOverdue = $item->due_date && $item->due_date->isPast();

            $student = $item->studentFee?->student;

            $out[] = [
                'student' => $student?->full_name,
                'admission_no' => $student?->admission_no,
                'academic_year' => $item->studentFee?->academicYear?->name,
                'category' => $item->feeCategory?->name,
                'amount' => (float) $item->amount,
                'paid' => (float) ($item->paid_sum ?? 0),
                'balance' => $balance,
                'due_date' => $item->due_date?->format('Y-m-d'),
                'overdue' => $isOverdue ? 'Yes' : 'No',
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function classWiseFeeReport(int $academicYearId): array
    {
        // Use SQL aggregation with GROUP BY instead of loading 20K rows into PHP
        $rows = StudentFeeItem::query()
            ->select([
                'class_section.id',
                DB::raw("CONCAT(school_classes.name, ' - ', sections.name) as class_label"),
                DB::raw('SUM(student_fee_items.amount) as total_due'),
                DB::raw('COALESCE(SUM(fpi.paid_amount), 0) as total_paid'),
            ])
            ->join('student_fees', 'student_fee_items.student_fee_id', '=', 'student_fees.id')
            ->join('students', 'student_fees.student_id', '=', 'students.id')
            ->leftJoin('student_sessions', function ($join) use ($academicYearId) {
                $join->on('students.id', '=', 'student_sessions.student_id')
                    ->where('student_sessions.academic_year_id', '=', $academicYearId)
                    ->where('student_sessions.status', '=', 'active');
            })
            ->leftJoin('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
            ->leftJoin('school_classes', 'class_section.class_id', '=', 'school_classes.id')
            ->leftJoin('sections', 'class_section.section_id', '=', 'sections.id')
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_amount FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id) GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->where('student_fees.academic_year_id', $academicYearId)
            ->groupBy('class_section.id', 'school_classes.name', 'sections.name')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $classLabel = $row->class_label ?? 'Unassigned';
            $due = (float) $row->total_due;
            $paid = (float) ($row->total_paid ?? 0);
            $balance = max(0, $due - $paid);

            $groups[] = [
                'class_section' => $classLabel,
                'total_due' => $due,
                'total_paid' => $paid,
                'balance' => $balance,
            ];
        }

        return $groups;
    }

    public function dashboardFeeStats(): array
    {
        $totalCollected = FeePayment::query()->sum('amount');
        $monthly = FeePayment::query()
            ->whereYear('paid_on', now()->year)
            ->whereMonth('paid_on', now()->month)
            ->sum('amount');

        // Replace chunkById loop with a single aggregate subquery for pending fees
        $pending = (float) StudentFeeItem::query()
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_sum FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id) GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->selectRaw('COALESCE(SUM(student_fee_items.amount - COALESCE(fpi.paid_sum, 0)), 0) as total_pending')
            ->value('total_pending');

        return [
            'total_collected' => (float) $totalCollected,
            'pending_fees' => max(0, (float) $pending),
            'monthly_collection' => (float) $monthly,
        ];
    }
}
