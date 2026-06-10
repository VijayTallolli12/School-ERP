<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\FeePaymentResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Repositories\FeeRepositoryInterface;
use App\Modules\Fees\Services\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeApiController extends ApiBaseController
{
    public function __construct(
        private readonly FeeRepositoryInterface $feeRepo,
        private readonly FeeService $feeService,
    ) {}

    public function studentFees(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'sometimes|nullable|integer|exists:students,id',
            'academic_year_id' => 'sometimes|nullable|integer|exists:academic_years,id',
            'status' => 'sometimes|nullable|in:paid,partial,pending,overdue',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $user = $request->user();

        if ($studentId = $request->integer('student_id')) {
            if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
                $guardian = $user->guardian;

                if (! $guardian || ! $guardian->students()->where('students.id', $studentId)->exists()) {
                    return $this->forbidden('You are not authorized to view fees for this student.');
                }
            }
        }

        $query = StudentFee::query()
            ->with(['student:id,first_name,last_name,admission_no,uuid', 'academicYear', 'items.feeCategory', 'items.paymentItems']);

        if ($studentId = $request->integer('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($academicYearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        $paginator = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (StudentFee $sf) => new StudentFeeResource($sf)),
            message: 'Student fees retrieved.'
        );
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

        $query = StudentFee::query()
            ->with(['student:id,first_name,last_name,admission_no,uuid', 'items.feeCategory', 'items.paymentItems', 'academicYear']);

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

        $query = FeePayment::query()
            ->with([
                'student:id,first_name,last_name,admission_no,uuid',
                'academicYear',
                'collector:id,name',
                'items.studentFeeItem.feeCategory',
            ]);

        if ($studentId = $request->integer('student_id')) {
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