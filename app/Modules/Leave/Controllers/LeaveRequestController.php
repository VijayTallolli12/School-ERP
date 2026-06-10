<?php

namespace App\Modules\Leave\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Leave\Repositories\LeaveRequestRepositoryInterface;
use App\Modules\Leave\Requests\StoreLeaveRequest;
use App\Modules\Leave\Services\LeaveService;
use App\Modules\Students\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LeaveRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly LeaveRequestRepositoryInterface $leaveRequests,
        private readonly LeaveService $service,
    ) {}

    public function index(): View
    {
        return view('modules.leave.requests.index', [
            'leaveTypes' => LeaveType::query()->active()->orderBy('name')->get(),
            'students' => Student::query()->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name']),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $cs) => $cs->schoolClass->sort_order.'-'.$cs->section->name),
            'academicYears' => AcademicYear::query()->where('status', 'active')->orderByDesc('starts_on')->get(),
            'statuses' => LeaveRequest::statuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->leaveRequests->query();

        if ($status = request('status')) {
            $query->where('leave_requests.status', $status);
        }

        if ($leaveTypeId = request('leave_type_id')) {
            $query->where('leave_requests.leave_type_id', $leaveTypeId);
        }

        if ($studentId = request('student_id')) {
            $query->where('leave_requests.student_id', $studentId);
        }

        if ($classSectionId = request('class_section_id')) {
            $query->whereHas('student.sessions', fn ($q) => $q->where('class_section_id', $classSectionId)->where('status', 'active'));
        }

        if ($fromDate = request('from_date')) {
            $query->where('leave_requests.from_date', '>=', $fromDate);
        }

        if ($toDate = request('to_date')) {
            $query->where('leave_requests.to_date', '<=', $toDate);
        }

        return DataTables::eloquent($query)
            ->addColumn('student_name', fn (LeaveRequest $lr) => e($lr->student?->full_name ?? 'Unknown'))
            ->addColumn('leave_type', fn (LeaveRequest $lr) => e($lr->leaveType?->name ?? '-'))
            ->addColumn('from_date', fn (LeaveRequest $lr) => $lr->from_date?->format('M d, Y'))
            ->addColumn('to_date', fn (LeaveRequest $lr) => $lr->to_date?->format('M d, Y'))
            ->addColumn('status_badge', fn (LeaveRequest $lr) => sprintf(
                '<span class="badge %s">%s</span>',
                $lr->status_badge,
                $lr->status_label
            ))
            ->addColumn('submitted_by', fn (LeaveRequest $lr) => e($lr->user?->name ?? '-'))
            ->addColumn('actions', fn (LeaveRequest $lr) => view('modules.leave.requests._actions', compact('lr'))->render())
            ->rawColumns(['status_badge', 'actions'])
            ->orderColumn('student_name', fn ($q, $direction) => $q->orderBy(
                Student::selectRaw("CONCAT_WS(' ', first_name, middle_name, last_name)")
                    ->whereColumn('students.id', 'leave_requests.student_id'),
                $direction
            ))
            ->filterColumn('student_name', fn ($q, $keyword) => $q->whereHas('student', fn ($sq) => $sq->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$keyword}%"])))
            ->toJson();
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $leaveRequest = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully.',
            'data' => $leaveRequest,
        ]);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $leaveRequest->load(['student', 'leaveType', 'user', 'approver']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leaveRequest->id,
                'student_name' => $leaveRequest->student?->full_name,
                'leave_type' => $leaveRequest->leaveType?->name,
                'from_date' => $leaveRequest->from_date?->toDateString(),
                'to_date' => $leaveRequest->to_date?->toDateString(),
                'days' => $leaveRequest->days,
                'reason' => $leaveRequest->reason,
                'attachment_url' => $leaveRequest->attachment_url,
                'status' => $leaveRequest->status,
                'status_label' => $leaveRequest->status_label,
                'status_badge' => $leaveRequest->status_badge,
                'submitted_by' => $leaveRequest->user?->name,
                'approved_by' => $leaveRequest->approver?->name,
                'approved_at' => $leaveRequest->approved_at?->format('M d, Y h:i A'),
                'remarks' => $leaveRequest->remarks,
                'created_at' => $leaveRequest->created_at?->format('M d, Y h:i A'),
            ],
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['remarks' => ['nullable', 'string', 'max:1000']]);

        $leaveRequest = $this->service->approve($leaveRequest, $request->input('remarks'));

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully.',
            'data' => $leaveRequest,
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['remarks' => ['nullable', 'string', 'max:1000']]);

        $leaveRequest = $this->service->reject($leaveRequest, $request->input('remarks'));

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected.',
            'data' => $leaveRequest,
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('delete', $leaveRequest);
        $this->service->delete($leaveRequest);

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully.',
        ]);
    }
}
