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
use App\Modules\Students\Models\StudentSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FeeService
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function pendingFeeItemsQuery(): Builder
    {
        return StudentFeeItem::query()
            ->whereHas('studentFee', function ($q): void {
                $q->where('school_id', $this->schoolContext->id())->where('status', 'active');
            })
            ->withSum(['paymentItems as paid_sum' => fn ($q) => $q->whereHas('feePayment', fn ($p) => $p->completed())], 'amount')
            ->havingRaw('COALESCE(paid_sum, 0) < student_fee_items.amount');
    }

    public function createFeeCategory(array $data): FeeCategory
    {
        $category = FeeCategory::query()->create($data);

        activity()->causedBy(auth()->user())->performedOn($category)->event('created')->log('Fee category created');

        return $category;
    }

    public function updateFeeCategory(FeeCategory $category, array $data): FeeCategory
    {
        $category->fill($data)->save();

        activity()->causedBy(auth()->user())->performedOn($category)->event('updated')->log('Fee category updated');

        return $category->refresh();
    }

    public function deleteFeeCategory(FeeCategory $category): void
    {
        if ($category->structureItems()->exists() || $category->studentFeeItems()->exists()) {
            throw new RuntimeException('This fee category is in use and cannot be deleted.');
        }

        $category->delete();

        activity()->causedBy(auth()->user())->performedOn($category)->event('deleted')->log('Fee category deleted');
    }

    public function createFeeStructure(array $data): FeeStructure
    {
        return DB::transaction(function () use ($data): FeeStructure {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $structure = FeeStructure::query()->create($data);
            $this->syncStructureItems($structure, $items);

            activity()->causedBy(auth()->user())->performedOn($structure)->event('created')->log('Fee structure created');

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

            activity()->causedBy(auth()->user())->performedOn($structure)->event('updated')->log('Fee structure updated');

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

        activity()->causedBy(auth()->user())->performedOn($structure)->event('deleted')->log('Fee structure deleted');
    }

    public function assignStudentFee(array $data): StudentFee
    {
        return DB::transaction(function () use ($data): StudentFee {
            $structure = FeeStructure::query()->with('items')->findOrFail($data['fee_structure_id']);

            if ((int) $structure->academic_year_id !== (int) $data['academic_year_id']) {
                throw new RuntimeException('The fee structure does not belong to the selected academic year.');
            }

            $this->assertStudentClassSectionMatches((int) $data['student_id'], (int) $data['academic_year_id'], $structure);

            $dueDate = isset($data['default_due_date']) ? Carbon::parse($data['default_due_date']) : null;

            $existing = StudentFee::query()
                ->where('student_id', $data['student_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->first();

            if ($existing) {
                throw new RuntimeException('This student already has a fee assignment for the selected academic year.');
            }

            try {
                $studentFee = StudentFee::query()->create([
                    'student_id' => $data['student_id'],
                    'academic_year_id' => $data['academic_year_id'],
                    'fee_structure_id' => $structure->id,
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] !== 1062) {
                    throw $e;
                }

                throw new RuntimeException('This student already has a fee assignment for the selected academic year.');
            }

            foreach ($structure->items as $line) {
                $studentFee->items()->create([
                    'fee_category_id' => $line->fee_category_id,
                    'amount' => $line->amount,
                    'due_date' => $dueDate,
                ]);
            }

            activity()->causedBy(auth()->user())->performedOn($studentFee)->event('created')->log('Fee assignment created');

            return $studentFee->load(['items.feeCategory', 'student', 'academicYear', 'feeStructure']);
        });
    }

    private function assertStudentClassSectionMatches(int $studentId, int $academicYearId, FeeStructure $structure): void
    {
        $session = StudentSession::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->first();

        if ($session && (int) $session->class_section_id !== (int) $structure->class_section_id) {
            throw new RuntimeException('The fee structure does not match the student\'s current class section.');
        }
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

                try {
                    $studentFee = StudentFee::query()->create([
                        'student_id' => $studentId,
                        'academic_year_id' => $data['academic_year_id'],
                        'fee_structure_id' => $structure->id,
                        'status' => 'active',
                        'assigned_at' => now(),
                    ]);
                } catch (QueryException $e) {
                    if ($e->errorInfo[1] !== 1062) {
                        throw $e;
                    }

                    $skipped++;
                    continue;
                }

                foreach ($structure->items as $line) {
                    $studentFee->items()->create([
                        'fee_category_id' => $line->fee_category_id,
                        'amount' => $line->amount,
                        'due_date' => $dueDate,
                    ]);
                }

                $assigned++;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($structure)
                ->event('created')
                ->withProperties(['assigned' => $assigned, 'skipped' => $skipped])
                ->log('Fee structure bulk-assigned to students');

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

                if ($this->itemHasCompletedPayments($item)) {
                    throw new RuntimeException('Fee lines with payments cannot be changed.');
                }

                $item->update([
                    'amount' => $row['amount'],
                    'due_date' => isset($row['due_date']) ? Carbon::parse($row['due_date']) : null,
                ]);
            }
        }

        activity()->causedBy(auth()->user())->performedOn($studentFee)->event('updated')->log('Fee assignment updated');

        return $studentFee->load(['items.feeCategory', 'student', 'academicYear', 'feeStructure']);
    }

    public function deleteStudentFee(StudentFee $studentFee): void
    {
        foreach ($studentFee->items as $item) {
            if ($this->itemHasCompletedPayments($item)) {
                throw new RuntimeException('Cannot remove a fee assignment that already has collections.');
            }
        }

        $studentFee->items()->delete();
        $studentFee->delete();

        activity()->causedBy(auth()->user())->performedOn($studentFee)->event('deleted')->log('Fee assignment deleted');
    }

    private function itemHasCompletedPayments(StudentFeeItem $item): bool
    {
        return FeePaymentItem::query()
            ->where('student_fee_item_id', $item->id)
            ->whereHas('feePayment', fn ($q) => $q->completed())
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
                    ->whereHas('studentFee', function ($q) use ($studentId, $academicYearId): void {
                        $q->where('student_id', $studentId)->where('academic_year_id', $academicYearId)->where('status', 'active');
                    })
                    ->whereKey($line['student_fee_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    throw new RuntimeException('One or more fee lines are not payable for this student and year.');
                }

                $paid = (float) $item->paymentItems()->whereHas('feePayment', fn ($q) => $q->completed())->sum('amount');
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

            activity()
                ->causedBy(auth()->user())
                ->performedOn($payment)
                ->event('created')
                ->withProperties([
                    'receipt_number' => $payment->receipt_number,
                    'amount' => $total,
                    'payment_mode' => $data['payment_mode'],
                    'paid_on' => $payment->paid_on?->toDateString(),
                ])
                ->log('Fee payment recorded');

            return $payment->load(['items.studentFeeItem.feeCategory', 'student', 'academicYear']);
        });
    }

    public function voidFeePayment(FeePayment $payment, ?string $reason): FeePayment
    {
        if ($payment->isVoided()) {
            throw new RuntimeException('This payment is already voided.');
        }

        if (! $reason || mb_strlen(trim($reason)) < 5) {
            throw new RuntimeException('A reason (at least 5 characters) is required to void a payment.');
        }

        $payment->update([
            'status' => FeePayment::STATUS_VOID,
            'void_reason' => trim($reason),
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->event('voided')
            ->withProperties([
                'receipt_number' => $payment->receipt_number,
                'amount' => $payment->amount,
                'reason' => trim($reason),
            ])
            ->log('Fee payment voided');

        return $payment->load(['items.studentFeeItem.feeCategory', 'student', 'academicYear']);
    }

    private function nextReceiptNumber(int $schoolId, int $academicYearId): string
    {
        $sequence = FeeReceiptSequence::query()->where('school_id', $schoolId)->where('academic_year_id', $academicYearId)->lockForUpdate()->first();

        if (! $sequence) {
            try {
                $sequence = FeeReceiptSequence::query()->create([
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYearId,
                    'last_number' => 0,
                ]);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] !== 1062) {
                    throw $e;
                }

                $sequence = FeeReceiptSequence::query()->where('school_id', $schoolId)->where('academic_year_id', $academicYearId)->lockForUpdate()->firstOrFail();
            }
        }

        $sequence->increment('last_number');

        return sprintf('RCP-%d-%d-%06d', $schoolId, $academicYearId, (int) $sequence->fresh()->last_number);
    }

    /**
     * @return EloquentCollection<int, StudentFeeItem>
     */
    public function listStudentFeeItemsForCollection(int $studentId, int $academicYearId): EloquentCollection
    {
        return StudentFeeItem::query()
            ->whereHas('studentFee', function ($q) use ($studentId, $academicYearId): void {
                $q->where('student_id', $studentId)->where('academic_year_id', $academicYearId)->where('status', 'active');
            })
            ->with('feeCategory')
            ->withSum(['paymentItems as paid_sum' => fn ($q) => $q->whereHas('feePayment', fn ($p) => $p->completed())], 'amount')
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function collectionReport(?string $from, ?string $to, ?int $classSectionId, ?string $paymentMode): array
    {
        $q = FeePayment::query()->completed()->with(['student', 'academicYear', 'collector']);

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
            ->whereHas('studentFee', function ($sq) use ($academicYearId): void {
                $sq->where('status', 'active');
                if ($academicYearId) {
                    $sq->where('academic_year_id', $academicYearId);
                }
            })
            ->with([
                'feeCategory',
                'studentFee.student',
                'studentFee.academicYear',
            ])
            ->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment', fn ($p) => $p->completed())], 'amount');

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
        $schoolId = $this->schoolContext->id();

        $rows = StudentFeeItem::query()
            ->select([
                'class_section.id',
                DB::raw("CONCAT(classes.name, ' - ', sections.name) as class_label"),
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
            ->leftJoin('classes', 'class_section.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'class_section.section_id', '=', 'sections.id')
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_amount FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id AND fee_payments.status = "completed") GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->where('student_fees.academic_year_id', $academicYearId)
            ->where('student_fees.status', 'active')
            ->when($schoolId, fn ($q) => $q->where('students.school_id', $schoolId))
            ->when($schoolId, fn ($q) => $q->where('class_section.school_id', $schoolId))
            ->groupBy('class_section.id', 'classes.name', 'sections.name')
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
        $totalCollected = FeePayment::query()->completed()->sum('amount');
        $monthly = FeePayment::query()->completed()
            ->whereYear('paid_on', now()->year)
            ->whereMonth('paid_on', now()->month)
            ->sum('amount');

        // Replace chunkById loop with a single aggregate subquery for pending fees
        $pending = (float) StudentFeeItem::query()
            ->join('student_fees', 'student_fees.id', '=', 'student_fee_items.student_fee_id')
            ->when($this->schoolContext->id(), fn ($q, $schoolId) => $q->where('student_fees.school_id', $schoolId))
            ->where('student_fees.status', 'active')
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_sum FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id AND fee_payments.status = "completed") GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->selectRaw('COALESCE(SUM(student_fee_items.amount - COALESCE(fpi.paid_sum, 0)), 0) as total_pending')
            ->value('total_pending');

        return [
            'total_collected' => (float) $totalCollected,
            'pending_fees' => max(0, (float) $pending),
            'monthly_collection' => (float) $monthly,
        ];
    }
}
