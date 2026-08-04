<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\FeePaymentResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Repositories\FeeRepositoryInterface;
use App\Modules\Fees\Services\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeeApiController extends ApiBaseController
{
    public function __construct(
        private readonly FeeRepositoryInterface $feeRepo,
        private readonly FeeService $feeService,
    ) {}

    public function studentFees(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['sometimes', 'nullable', 'integer', Rule::exists('students', 'id')->where('school_id', app(SchoolContext::class)->id())],
            'academic_year_id' => ['sometimes', 'nullable', 'integer', Rule::exists('academic_years', 'id')->where('school_id', app(SchoolContext::class)->id())],
            'status' => 'sometimes|nullable|in:paid,partial,pending,overdue',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $user = $request->user();
        $studentId = $request->integer('student_id');

        $query = StudentFee::query()
            ->with([
                'student:id,first_name,last_name,admission_no,uuid',
                'academicYear',
                'items.feeCategory',
                'items' => fn ($q) => $q->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment', fn ($p) => $p->completed())], 'amount'),
            ]);

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
            $guardian = $user->guardian;

            if (! $guardian) {
                return $this->forbidden('You are not authorized to view student fees.');
            }

            $ownStudentIds = $guardian->students()->pluck('students.id');

            if ($studentId) {
                if (! $ownStudentIds->contains($studentId)) {
                    return $this->forbidden('You are not authorized to view fees for this student.');
                }

                $query->where('student_id', $studentId);
            } else {
                if ($ownStudentIds->isEmpty()) {
                    return $this->forbidden('You are not authorized to view student fees.');
                }

                $query->whereIn('student_id', $ownStudentIds);
            }
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($academicYearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        $this->applyStatusFilter($query, $request->input('status'));

        $paginator = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (StudentFee $sf) => new StudentFeeResource($sf)),
            message: 'Student fees retrieved.'
        );
    }

    private function applyStatusFilter($query, ?string $status): void
    {
        if (! $status) {
            return;
        }

        $paidSubquery = DB::raw('COALESCE((SELECT SUM(fpi.amount) FROM fee_payment_items fpi WHERE fpi.student_fee_item_id = student_fee_items.id AND EXISTS (SELECT 1 FROM fee_payments fp WHERE fp.id = fpi.fee_payment_id AND fp.status = "completed")), 0)');

        $hasBalance = function ($q) use ($paidSubquery): void {
            $q->whereColumn('student_fee_items.amount', '>', $paidSubquery);
        };

        $fullyPaid = function ($q) use ($paidSubquery): void {
            $q->whereColumn('student_fee_items.amount', '<=', $paidSubquery);
        };

        switch ($status) {
            case 'paid':
                $query->whereDoesntHave('items', $hasBalance);
                break;
            case 'pending':
                $query->whereHas('items', $hasBalance);
                break;
            case 'partial':
                $query->whereHas('items', $hasBalance)->whereHas('items', $fullyPaid);
                break;
            case 'overdue':
                $query->whereHas('items', function ($q) use ($hasBalance): void {
                    $hasBalance($q);
                    $q->whereDate('student_fee_items.due_date', '<', now());
                });
                break;
        }
    }

    public function paymentReceipt(int $paymentId): JsonResponse
    {
        $user = request()->user();
        $payment = FeePayment::query()
            ->with([
                'student:id,first_name,last_name,admission_no,uuid',
                'academicYear',
                'collector:id,name',
                'items.studentFeeItem.feeCategory',
            ])
            ->find($paymentId);

        if (! $payment) {
            return $this->notFound('Payment not found.');
        }

        if ($payment->isVoided()) {
            return $this->error('This receipt has been voided and is no longer valid.', 422);
        }

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
            $guardian = $user->guardian;

            if (! $guardian || ! $guardian->students()->where('students.id', $payment->student_id)->exists()) {
                return $this->forbidden('You are not authorized to view this receipt.');
            }
        }

        return $this->success(new FeePaymentResource($payment), 'Payment receipt retrieved.');
    }

    public function pendingFees(Request $request): JsonResponse
    {
        $request->validate([
            'class_section_id' => 'sometimes|nullable|integer|exists:class_section,id',
            'academic_year_id' => 'sometimes|nullable|integer|exists:academic_years,id',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
            return $this->forbidden('You are not authorized to view pending fees.');
        }

        $query = StudentFee::query()
            ->with([
                'student:id,first_name,last_name,admission_no,uuid',
                'items.feeCategory',
                'items' => fn ($q) => $q->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment', fn ($p) => $p->completed())], 'amount'),
                'academicYear',
            ]);

        if ($classSectionId = $request->integer('class_section_id')) {
            $query->whereHas('student.currentSession', fn ($q) => $q->where('class_section_id', $classSectionId));
        }

        if ($academicYearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        // Only fees with unpaid/partial items
        $query->whereHas('items', function ($q): void {
            $q->whereColumn('amount', '>', \DB::raw('COALESCE((SELECT SUM(fpi.amount) FROM fee_payment_items fpi WHERE fpi.student_fee_item_id = student_fee_items.id), 0)'));
        });

        $paginator = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (StudentFee $sf) => new StudentFeeResource($sf)),
            message: 'Pending fees retrieved.'
        );
    }

    public function payments(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'sometimes|nullable|integer|exists:students,id',
            'from' => 'sometimes|nullable|date_format:Y-m-d',
            'to' => 'sometimes|nullable|date_format:Y-m-d|after_or_equal:from',
            'payment_mode' => 'sometimes|nullable|string|in:' . implode(',', array_keys(FeePayment::paymentModes())),
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = FeePayment::query()->completed()
            ->with([
                'student:id,first_name,last_name,admission_no,uuid',
                'academicYear',
                'collector:id,name',
                'items.studentFeeItem.feeCategory',
            ]);

        $user = $request->user();
        $studentId = $request->integer('student_id');

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
            $guardian = $user->guardian;

            if (! $guardian) {
                return $this->forbidden('You are not authorized to view payments.');
            }

            $ownStudentIds = $guardian->students()->pluck('students.id');

            if ($studentId) {
                if (! $ownStudentIds->contains($studentId)) {
                    return $this->forbidden('You are not authorized to view payments for this student.');
                }

                $query->where('student_id', $studentId);
            } else {
                if ($ownStudentIds->isEmpty()) {
                    return $this->forbidden('You are not authorized to view payments.');
                }

                $query->whereIn('student_id', $ownStudentIds);
            }
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('paid_on', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('paid_on', '<=', $to);
        }

        if ($paymentMode = $request->input('payment_mode')) {
            $query->where('payment_mode', $paymentMode);
        }

        $paginator = $query->orderByDesc('paid_on')->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (FeePayment $fp) => new FeePaymentResource($fp)),
            message: 'Payment history retrieved.'
        );
    }

    public function dashboardStats(): JsonResponse
    {
        $stats = $this->feeService->dashboardFeeStats();

        return $this->success($stats, 'Fee dashboard stats retrieved.');
    }
}