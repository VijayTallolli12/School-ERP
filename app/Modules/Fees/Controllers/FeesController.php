<?php

namespace App\Modules\Fees\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Repositories\FeeRepositoryInterface;
use App\Modules\Fees\Requests\AssignStudentFeeRequest;
use App\Modules\Fees\Requests\BulkAssignStudentFeesRequest;
use App\Modules\Fees\Requests\FeeReportFilterRequest;
use App\Modules\Fees\Requests\SaveFeeCategoryRequest;
use App\Modules\Fees\Requests\SaveFeeStructureRequest;
use App\Modules\Fees\Requests\StoreFeePaymentRequest;
use App\Modules\Fees\Requests\UpdateStudentFeeRequest;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Students\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class FeesController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly FeeRepositoryInterface $fees,
        private readonly FeeService $service,
    ) {}

    public function index(): View
    {
        return view('modules.fees.index', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $cs) => $cs->schoolClass->sort_order.'-'.$cs->section->name),
            'paymentModes' => FeePayment::paymentModes(),
            'feeCategories' => FeeCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'students' => Student::query()
                ->where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'admission_no', 'first_name', 'middle_name', 'last_name']),
            'structuresForSelect' => FeeStructure::query()
                ->with(['academicYear', 'classSection.schoolClass', 'classSection.section'])
                ->get()
                ->map(fn (FeeStructure $s) => [
                    'id' => $s->id,
                    'label' => ($s->academicYear?->name ?? '').' — '.($s->classSection?->schoolClass?->name ?? '').' '.($s->classSection?->section?->name ?? ''),
                    'academic_year_id' => $s->academic_year_id,
                    'class_section_id' => $s->class_section_id,
                ]),
        ]);
    }

    public function categoriesData(): JsonResponse
    {
        return DataTables::of($this->fees->feeCategoriesQuery())
            ->addColumn('actions', fn (FeeCategory $row) => view('modules.fees._actions_category', ['row' => $row])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function storeCategory(SaveFeeCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', FeeCategory::class);

        return $this->jsonCreated('Fee category saved.', $this->service->createFeeCategory($request->validated()));
    }

    public function showCategory(FeeCategory $fee_category): JsonResponse
    {
        $this->authorize('view', $fee_category);

        return $this->jsonData([
            'id' => $fee_category->id,
            'code' => $fee_category->code,
            'name' => $fee_category->name,
            'description' => $fee_category->description,
            'sort_order' => $fee_category->sort_order,
        ]);
    }

    public function updateCategory(SaveFeeCategoryRequest $request, FeeCategory $fee_category): JsonResponse
    {
        $this->authorize('update', $fee_category);

        return $this->jsonCreated('Fee category updated.', $this->service->updateFeeCategory($fee_category, $request->validated()));
    }

    public function destroyCategory(FeeCategory $fee_category): JsonResponse
    {
        $this->authorize('delete', $fee_category);

        try {
            $this->service->deleteFeeCategory($fee_category);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonMessage('Fee category deleted.');
    }

    public function structuresData(): JsonResponse
    {
        return DataTables::of($this->fees->feeStructuresQuery())
            ->addColumn('name', fn (FeeStructure $row) => e($row->name ?: '-'))
            ->addColumn('academic_year', fn (FeeStructure $row) => e($row->academicYear?->name ?? '-'))
            ->addColumn('class_section', fn (FeeStructure $row) => e(($row->classSection?->schoolClass?->name ?? '').' - '.($row->classSection?->section?->name ?? '')))
            ->addColumn('totals', function (FeeStructure $row): string {
                $sum = $row->items->sum(fn ($i) => (float) $i->amount);

                return e(number_format($sum, 2));
            })
            ->addColumn('status', fn (FeeStructure $row) => $row->status === 'active'
                ? '<span class="badge text-bg-success">Active</span>'
                : '<span class="badge text-bg-secondary">Inactive</span>')
            ->addColumn('actions', fn (FeeStructure $row) => view('modules.fees._actions_structure', ['row' => $row])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeStructure(SaveFeeStructureRequest $request): JsonResponse
    {
        $this->authorize('create', FeeStructure::class);

        try {
            $structure = $this->service->createFeeStructure($request->structurePayload());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonCreated('Fee structure created.', $structure);
    }

    public function showStructure(FeeStructure $fee_structure): JsonResponse
    {
        $this->authorize('view', $fee_structure);
        $fee_structure->load('items.feeCategory');

        return $this->jsonData([
            'id' => $fee_structure->id,
            'academic_year_id' => $fee_structure->academic_year_id,
            'class_section_id' => $fee_structure->class_section_id,
            'name' => $fee_structure->name,
            'status' => $fee_structure->status,
            'items' => $fee_structure->items->map(fn ($i) => [
                'fee_category_id' => $i->fee_category_id,
                'amount' => $i->amount,
            ]),
        ]);
    }

    public function updateStructure(SaveFeeStructureRequest $request, FeeStructure $fee_structure): JsonResponse
    {
        $this->authorize('update', $fee_structure);

        try {
            $structure = $this->service->updateFeeStructure($fee_structure, $request->structurePayload());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonCreated('Fee structure updated.', $structure);
    }

    public function destroyStructure(FeeStructure $fee_structure): JsonResponse
    {
        $this->authorize('delete', $fee_structure);

        try {
            $this->service->deleteFeeStructure($fee_structure);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonMessage('Fee structure deleted.');
    }

    public function assignmentsData(): JsonResponse
    {
        return DataTables::of($this->fees->studentFeesQuery())
            ->addColumn('student', fn (StudentFee $row) => e($row->student?->full_name ?? '-'))
            ->addColumn('admission_no', fn (StudentFee $row) => e($row->student?->admission_no ?? '-'))
            ->addColumn('academic_year', fn (StudentFee $row) => e($row->academicYear?->name ?? '-'))
            ->addColumn('class_section', fn (StudentFee $row) => e(
                ($row->feeStructure?->classSection?->schoolClass?->name ?? '').' - '.($row->feeStructure?->classSection?->section?->name ?? '')
            ))
            ->addColumn('status', fn (StudentFee $row) => e($row->status))
            ->addColumn('total_due', function (StudentFee $row): string {
                $sum = $row->items->sum(fn ($i) => (float) $i->amount);

                return e(number_format($sum, 2));
            })
            ->addColumn('actions', fn (StudentFee $row) => view('modules.fees._actions_assignment', ['row' => $row])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function storeAssignment(AssignStudentFeeRequest $request): JsonResponse
    {
        $this->authorize('create', StudentFee::class);

        try {
            $assignment = $this->service->assignStudentFee($request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonCreated('Fee assigned to student.', $assignment);
    }

    public function bulkAssignments(BulkAssignStudentFeesRequest $request): JsonResponse
    {
        $this->authorize('create', StudentFee::class);

        try {
            $result = $this->service->bulkAssignStudentFees($request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Assigned {$result['assigned']} students. Skipped {$result['skipped']} (already assigned).",
            'data' => $result,
        ]);
    }

    public function showAssignment(StudentFee $student_fee): JsonResponse
    {
        $this->authorize('view', $student_fee);
        $student_fee->load(['items.feeCategory', 'student', 'academicYear', 'feeStructure']);

        return $this->jsonData([
            'id' => $student_fee->id,
            'student_id' => $student_fee->student_id,
            'academic_year_id' => $student_fee->academic_year_id,
            'fee_structure_id' => $student_fee->fee_structure_id,
            'status' => $student_fee->status,
            'items' => $student_fee->items->map(fn ($i) => [
                'id' => $i->id,
                'fee_category_id' => $i->fee_category_id,
                'category_name' => $i->feeCategory?->name,
                'amount' => $i->amount,
                'due_date' => $i->due_date?->toDateString(),
            ]),
        ]);
    }

    public function updateAssignment(UpdateStudentFeeRequest $request, StudentFee $student_fee): JsonResponse
    {
        $this->authorize('update', $student_fee);

        try {
            $assignment = $this->service->updateStudentFee($student_fee, $request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonCreated('Student fee updated.', $assignment);
    }

    public function destroyAssignment(StudentFee $student_fee): JsonResponse
    {
        $this->authorize('delete', $student_fee);

        try {
            $this->service->deleteStudentFee($student_fee);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonMessage('Student fee assignment removed.');
    }

    public function collectionsData(): JsonResponse
    {
        return DataTables::of($this->fees->feePaymentsQuery())
            ->addColumn('academic_year', fn (FeePayment $row) => e($row->academicYear?->name ?? '-'))
            ->editColumn('amount', fn (FeePayment $row) => number_format((float) $row->amount, 2))
            ->editColumn('paid_on', fn (FeePayment $row) => $row->paid_on?->format('d-M-Y'))
            ->addColumn('student', fn (FeePayment $row) => e($row->student?->full_name ?? '-'))
            ->addColumn('mode_label', fn (FeePayment $row) => e(FeePayment::paymentModes()[$row->payment_mode] ?? $row->payment_mode))
            ->addColumn('actions', fn (FeePayment $row) => view('modules.fees._actions_collection', ['row' => $row])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function storeCollection(StoreFeePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', FeePayment::class);

        try {
            $payment = $this->service->recordPayment($request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->jsonCreated('Payment recorded.', $payment);
    }

    public function destroyCollection(FeePayment $fee_payment): JsonResponse
    {
        $this->authorize('delete', $fee_payment);
        $this->service->deleteFeePayment($fee_payment);

        return $this->jsonMessage('Payment removed.');
    }

    public function studentFeeItems(Request $request): JsonResponse
    {
        $this->authorize('create', FeePayment::class);
        $request->validate([
            'student_id' => ['required', 'integer'],
            'academic_year_id' => ['required', 'integer'],
        ]);

        $items = $this->service->listStudentFeeItemsForCollection(
            (int) $request->query('student_id'),
            (int) $request->query('academic_year_id')
        );

        return response()->json([
            'success' => true,
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'category' => $i->feeCategory?->name,
                'amount' => (float) $i->amount,
                'paid' => (float) ($i->paid_sum ?? 0),
                'balance' => max(0, (float) $i->amount - (float) ($i->paid_sum ?? 0)),
            ]),
        ]);
    }

    public function duesData(): JsonResponse
    {
        $rows = $this->fees->studentFeeItemsDueBaseQuery()
            ->orderBy('due_date')
            ->limit(5000)
            ->get()
            ->map(function ($item) {
                $balance = max(0, (float) $item->amount - (float) ($item->paid_sum ?? 0));

                return (object) [
                    'id' => $item->id,
                    'student_name' => $item->studentFee?->student?->full_name,
                    'admission_no' => $item->studentFee?->student?->admission_no,
                    'academic_year' => $item->studentFee?->academicYear?->name,
                    'category' => $item->feeCategory?->name,
                    'amount' => (float) $item->amount,
                    'paid' => (float) ($item->paid_sum ?? 0),
                    'balance' => $balance,
                    'due_date' => $item->due_date?->format('Y-m-d'),
                    'overdue' => $item->due_date && $item->due_date->isPast() && $balance > 0.009,
                ];
            })
            ->filter(fn ($r) => $r->balance > 0.009)
            ->values();

        return DataTables::of($rows)
            ->addColumn('overdue_badge', fn ($row) => $row->overdue
                ? '<span class="badge bg-danger">Overdue</span>'
                : '<span class="badge bg-secondary">Pending</span>')
            ->rawColumns(['overdue_badge'])
            ->toJson();
    }

    public function reportCollection(FeeReportFilterRequest $request): View
    {
        $filters = $request->filterPayload();
        $rows = $this->service->collectionReport(
            $filters['from_date'] ?? null,
            $filters['to_date'] ?? null,
            isset($filters['class_section_id']) ? (int) $filters['class_section_id'] : null,
            $filters['payment_mode'] ?? null,
        );

        return view('modules.fees.reports.print_collection', [
            'title' => 'Fee Collection Report',
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function reportCollectionPdf(FeeReportFilterRequest $request)
    {
        $filters = $request->filterPayload();
        $rows = $this->service->collectionReport(
            $filters['from_date'] ?? null,
            $filters['to_date'] ?? null,
            isset($filters['class_section_id']) ? (int) $filters['class_section_id'] : null,
            $filters['payment_mode'] ?? null,
        );

        return Pdf::loadView('modules.fees.reports.print_collection', [
            'title' => 'Fee Collection Report',
            'rows' => $rows,
            'filters' => $filters,
        ])
            ->setPaper('a4', 'landscape')
            ->download('fee-collection-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function reportDue(FeeReportFilterRequest $request): View
    {
        $filters = $request->filterPayload();
        $rows = $this->service->dueReport(
            isset($filters['academic_year_id']) ? (int) $filters['academic_year_id'] : null,
            (bool) ($filters['overdue_only'] ?? false),
        );

        return view('modules.fees.reports.print_due', [
            'title' => 'Fee Due Report',
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function reportDuePdf(FeeReportFilterRequest $request)
    {
        $filters = $request->filterPayload();
        $rows = $this->service->dueReport(
            isset($filters['academic_year_id']) ? (int) $filters['academic_year_id'] : null,
            (bool) ($filters['overdue_only'] ?? false),
        );

        return Pdf::loadView('modules.fees.reports.print_due', [
            'title' => 'Fee Due Report',
            'rows' => $rows,
            'filters' => $filters,
        ])
            ->setPaper('a4', 'landscape')
            ->download('fee-due-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function reportClassWise(FeeReportFilterRequest $request): View
    {
        $filters = $request->filterPayload();
        $academicYearId = (int) ($filters['academic_year_id'] ?? 0);
        $rows = $academicYearId ? $this->service->classWiseFeeReport($academicYearId) : [];

        return view('modules.fees.reports.print_class_wise', [
            'title' => 'Class-wise Fee Report',
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function reportClassWisePdf(FeeReportFilterRequest $request)
    {
        $filters = $request->filterPayload();
        $academicYearId = (int) ($filters['academic_year_id'] ?? 0);
        $rows = $academicYearId ? $this->service->classWiseFeeReport($academicYearId) : [];

        return Pdf::loadView('modules.fees.reports.print_class_wise', [
            'title' => 'Class-wise Fee Report',
            'rows' => $rows,
            'filters' => $filters,
        ])
            ->setPaper('a4', 'portrait')
            ->download('fee-class-wise-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function reportDaily(FeeReportFilterRequest $request): View
    {
        $filters = $request->filterPayload();
        $date = $filters['report_date'] ?? now()->toDateString();
        $rows = $this->service->dailyCollectionReport($date);

        return view('modules.fees.reports.print_collection', [
            'title' => 'Daily Fee Collection — '.$date,
            'rows' => $rows,
            'filters' => array_merge($filters, ['from_date' => $date, 'to_date' => $date]),
        ]);
    }

    public function reportDailyPdf(FeeReportFilterRequest $request)
    {
        $filters = $request->filterPayload();
        $date = $filters['report_date'] ?? now()->toDateString();
        $rows = $this->service->dailyCollectionReport($date);

        return Pdf::loadView('modules.fees.reports.print_collection', [
            'title' => 'Daily Fee Collection — '.$date,
            'rows' => $rows,
            'filters' => $filters,
        ])
            ->setPaper('a4', 'landscape')
            ->download('fee-daily-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function receiptPrint(FeePayment $fee_payment): View
    {
        $this->authorize('view', $fee_payment);
        $fee_payment->load(['student', 'academicYear', 'collector', 'items.studentFeeItem.feeCategory']);

        return view('modules.fees.receipt_print', ['payment' => $fee_payment]);
    }

    public function receiptPdf(FeePayment $fee_payment)
    {
        $this->authorize('view', $fee_payment);
        $fee_payment->load(['student', 'academicYear', 'collector', 'items.studentFeeItem.feeCategory']);

        return Pdf::loadView('modules.fees.receipt_print', ['payment' => $fee_payment])
            ->setPaper('a4', 'portrait')
            ->download('receipt-'.$fee_payment->receipt_number.'.pdf');
    }

    private function jsonCreated(string $message, mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function jsonData(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function jsonMessage(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
