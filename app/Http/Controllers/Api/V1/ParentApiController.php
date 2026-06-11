<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\ExamResultResource;
use App\Http\Resources\Api\V1\HomeworkResource;
use App\Http\Resources\Api\V1\ParentListResource;
use App\Http\Resources\Api\V1\ParentResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Http\Resources\Api\V1\StudentListResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Homework\Models\Homework;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use App\Modules\Parents\Services\ParentService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentDocument;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ParentApiController extends ApiBaseController
{
    public function __construct(
        private readonly ParentRepositoryInterface $parentRepo,
        private readonly ParentService $parentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'sometimes|nullable|string|max:100',
            'status' => 'sometimes|nullable|in:active,inactive',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Guardian::query()->withCount('students');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('first_name')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Guardian $g) => new ParentListResource($g)),
            message: 'Parents retrieved.'
        );
    }

    public function show(string $uuid): JsonResponse
    {
        $parent = Guardian::query()
            ->where('uuid', $uuid)
            ->with(['students', 'user'])
            ->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        return $this->success(new ParentResource($parent), 'Parent retrieved.');
    }

    public function children(string $uuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $students = $parent->students()
            ->with(['currentSession.classSection.schoolClass', 'currentSession.classSection.section'])
            ->get();

        return $this->success(
            StudentListResource::collection($students),
            'Linked children retrieved.'
        );
    }

    public function childAttendance(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $month = request()->integer('month', (int) now()->month);
        $year = request()->integer('year', (int) now()->year);

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['classSection.schoolClass', 'classSection.section', 'markedBy:id,name'])
            ->orderBy('attendance_date')
            ->get();

        $statuses = Attendance::getStatuses();
        $counts = [];
        foreach ($statuses as $key => $label) {
            $counts[$key] = $records->where('status', $key)->count();
        }

        return $this->success([
            'student' => new StudentListResource($student),
            'month' => $month,
            'year' => $year,
            'summary' => [
                'total_days' => $records->count(),
                'counts' => $counts,
            ],
            'records' => AttendanceResource::collection($records),
        ], 'Child attendance retrieved.');
    }

    public function childFees(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $fees = StudentFee::query()
            ->where('student_id', $student->id)
            ->with(['academicYear', 'items.feeCategory', 'items.paymentItems'])
            ->orderByDesc('id')
            ->get();

        return $this->success(
            StudentFeeResource::collection($fees),
            'Child fees retrieved.'
        );
    }

    public function childExamResults(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $results = ExamResult::query()
            ->where('student_id', $student->id)
            ->with(['exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('exam.academic_year_id');

        return $this->success([
            'student' => new StudentListResource($student),
            'results_by_academic_year' => $results->map(fn ($group) => ExamResultResource::collection($group)),
        ], 'Child exam results retrieved.');
    }

    public function childTimetable(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $currentSession = $student->currentSession()->first();

        if (! $currentSession) {
            return $this->success(['timetable' => []], 'No active session.');
        }

        $slots = TimetableSlot::query()
            ->where('class_section_id', $currentSession->class_section_id)
            ->where('academic_year_id', $currentSession->academic_year_id)
            ->with(['subject:id,name,code', 'teacher.user:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($daySlots) => $daySlots->map(fn ($slot) => [
                'id' => $slot->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'subject' => $slot->subject ? ['id' => $slot->subject->id, 'name' => $slot->subject->name] : null,
                'teacher' => $slot->teacher?->user ? ['id' => $slot->teacher->user->id, 'name' => $slot->teacher->user->name] : null,
                'room' => $slot->room,
            ]));

        return $this->success(['timetable' => $slots], 'Child timetable retrieved.');
    }

    public function dashboard(string $uuid, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('students')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $childUuid = $request->query('child_uuid');

        $data = $this->parentService->getParentDashboardData($parent, $childUuid);

        return $this->success($data, 'Parent dashboard retrieved.');
    }

    public function childHomework(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $currentSession = $student->currentSession()->first();

        if (! $currentSession) {
            return $this->success(['homework' => []], 'No active session.');
        }

        $homework = Homework::query()
            ->where('class_section_id', $currentSession->class_section_id)
            ->where('academic_year_id', $currentSession->academic_year_id)
            ->with(['subject:id,name', 'classSection.schoolClass', 'classSection.section'])
            ->active()
            ->orderByDesc('assigned_date')
            ->get();

        return $this->success([
            'student' => new StudentListResource($student),
            'homework' => HomeworkResource::collection($homework),
        ], 'Child homework retrieved.');
    }

    public function childCalendar(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $month = request()->integer('month', (int) now()->month);
        $year = request()->integer('year', (int) now()->year);
        $type = request()->input('type');

        $query = AcademicCalendar::query()
            ->published()
            ->where(function ($q) {
                $q->where('audience', 'all')
                    ->orWhere('audience', 'parents')
                    ->orWhere('audience', 'students');
            })
            ->byMonth($year, $month)
            ->orderBy('start_date');

        if ($type) {
            $query->where('event_type', $type);
        }

        $events = $query->get();

        return $this->success([
            'student' => new StudentListResource($student),
            'month' => $month,
            'year' => $year,
            'events' => $events,
        ], 'Child calendar retrieved.');
    }

    public function childDocuments(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $documents = StudentDocument::query()
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (StudentDocument $doc) => [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'document_type_label' => $doc->document_type_label,
                'title' => $doc->title,
                'file_name' => $doc->file_name,
                'file_size' => $doc->file_size,
                'file_size_formatted' => $doc->file_size_formatted,
                'mime_type' => $doc->mime_type,
                'issue_date' => $doc->issue_date?->format('Y-m-d'),
                'expiry_date' => $doc->expiry_date?->format('Y-m-d'),
                'is_verified' => $doc->is_verified,
                'verification_status_label' => $doc->verification_status_label,
                'remarks' => $doc->remarks,
                'download_url' => $doc->download_url,
                'created_at' => $doc->created_at?->toISOString(),
            ]);

        return $this->success([
            'student' => new StudentListResource($student),
            'documents' => $documents,
        ], 'Child documents retrieved.');
    }

    public function childCirculars(string $uuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $perPage = request()->integer('per_page', 15);

        $paginator = Notification::query()
            ->where('target_type', 'parents')
            ->where('type', 'announcement')
            ->where('status', 'sent')
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (Notification $notification) use ($parent) {
            $pivot = $notification->users()
                ->where('user_id', $parent->user_id)
                ->first()?->pivot;

            return $this->formatCircular($notification, $pivot);
        });

        return $this->paginated(
            paginator: $paginator,
            message: 'Circulars retrieved.'
        );
    }

    public function childCircularDetail(string $uuid, int $id): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $notification = Notification::query()
            ->where('target_type', 'parents')
            ->where('type', 'announcement')
            ->where('id', $id)
            ->with('creator:id,name')
            ->first();

        if (! $notification) {
            return $this->notFound('Circular not found.');
        }

        $pivot = $notification->users()
            ->where('user_id', $parent->user_id)
            ->first()?->pivot;

        return $this->success(
            $this->formatCircular($notification, $pivot),
            'Circular retrieved.'
        );
    }

    public function markCircularRead(string $uuid, int $id): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $notification = Notification::query()
            ->where('target_type', 'parents')
            ->where('type', 'announcement')
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return $this->notFound('Circular not found.');
        }

        $notification->users()->syncWithoutDetaching([
            $parent->user_id => [
                'is_read' => true,
                'read_at' => now(),
                'delivery_status' => 'delivered',
            ],
        ]);

        $notification->load('creator:id,name');
        $pivot = $notification->users()
            ->where('user_id', $parent->user_id)
            ->first()?->pivot;

        return $this->success(
            $this->formatCircular($notification, $pivot),
            'Circular marked as read.'
        );
    }

    private function formatCircular(Notification $notification, $pivot): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->message,
            'message' => $notification->message,
            'type' => $notification->type,
            'type_label' => $notification->type_label,
            'priority' => $notification->priority,
            'sent_at' => $notification->sent_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'is_read' => $pivot ? (bool) $pivot->is_read : false,
            'read_at' => $pivot ? $pivot->read_at : null,
            'created_by' => $notification->relationLoaded('creator') && $notification->creator
                ? ['id' => $notification->creator->id, 'name' => $notification->creator->name]
                : null,
        ];
    }

    public function childLeaveRequests(string $uuid, string $childUuid): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $requests = LeaveRequest::query()
            ->where('student_id', $student->id)
            ->with(['leaveType', 'student'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (LeaveRequest $lr) => [
                'id' => $lr->id,
                'student_id' => $lr->student_id,
                'student_name' => $lr->student?->name,
                'leave_type_id' => $lr->leave_type_id,
                'leave_type' => $lr->leaveType?->name,
                'from_date' => $lr->from_date?->format('Y-m-d'),
                'to_date' => $lr->to_date?->format('Y-m-d'),
                'days' => $lr->days,
                'reason' => $lr->reason,
                'status' => $lr->status,
                'status_label' => $lr->status_label,
                'attachment_url' => $lr->attachment_url,
                'remarks' => $lr->remarks,
                'created_at' => $lr->created_at?->toISOString(),
            ]);

        return $this->success([
            'student' => new StudentListResource($student),
            'leave_requests' => $requests,
        ], 'Leave requests retrieved.');
    }

    public function storeLeaveRequest(string $uuid, string $childUuid, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $validated = Validator::make($request->all(), [
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'leave_type' => 'required_without:leave_type_id|string|max:100',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ])->validate();

        $from = \Carbon\Carbon::parse($validated['from_date']);
        $to = \Carbon\Carbon::parse($validated['to_date']);
        $days = $from->diffInDays($to) + 1;

        $leaveTypeId = $validated['leave_type_id'] ?? null;
        if (! $leaveTypeId) {
            $leaveTypeName = $validated['leave_type'] ?? 'General';
            $leaveType = \App\Modules\Leave\Models\LeaveType::query()
                ->where('school_id', $student->school_id)
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($leaveTypeName) . '%'])
                ->first();
            if (! $leaveType) {
                $leaveType = \App\Modules\Leave\Models\LeaveType::firstOrCreate(
                    ['school_id' => $student->school_id, 'name' => ucfirst(strtolower($leaveTypeName))],
                    ['name' => ucfirst(strtolower($leaveTypeName)), 'is_active' => true],
                );
            }
            $leaveTypeId = $leaveType->id;
        }

        $leaveRequest = new LeaveRequest();
        $leaveRequest->school_id = $student->school_id;
        $leaveRequest->user_id = $parent->user_id;
        $leaveRequest->student_id = $student->id;
        $leaveRequest->leave_type_id = $leaveTypeId;
        $leaveRequest->from_date = $validated['from_date'];
        $leaveRequest->to_date = $validated['to_date'];
        $leaveRequest->days = $days;
        $leaveRequest->reason = $validated['reason'];
        $leaveRequest->status = 'pending';
        $leaveRequest->created_by = $parent->user_id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-attachments', 'public');
            $leaveRequest->attachment = $path;
        }

        $leaveRequest->save();

        return $this->created([
            'leave_request' => $leaveRequest->fresh()->load('leaveType'),
        ], 'Leave request submitted successfully.');
    }

    public function updateLeaveRequest(string $uuid, string $childUuid, int $id, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $leaveRequest = LeaveRequest::query()
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (! $leaveRequest) {
            return $this->notFound('Leave request not found.');
        }

        if ($leaveRequest->status !== 'pending') {
            return $this->error('Only pending leave requests can be edited.', 422);
        }

        $validated = Validator::make($request->all(), [
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'leave_type' => 'required_without:leave_type_id|string|max:100',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ])->validate();

        $from = \Carbon\Carbon::parse($validated['from_date']);
        $to = \Carbon\Carbon::parse($validated['to_date']);
        $days = $from->diffInDays($to) + 1;

        $leaveTypeId = $validated['leave_type_id'] ?? null;
        if (! $leaveTypeId) {
            $leaveTypeName = $validated['leave_type'] ?? 'General';
            $leaveType = \App\Modules\Leave\Models\LeaveType::query()
                ->where('school_id', $student->school_id)
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($leaveTypeName) . '%'])
                ->first();
            if (! $leaveType) {
                $leaveType = \App\Modules\Leave\Models\LeaveType::firstOrCreate(
                    ['school_id' => $student->school_id, 'name' => ucfirst(strtolower($leaveTypeName))],
                    ['name' => ucfirst(strtolower($leaveTypeName)), 'is_active' => true],
                );
            }
            $leaveTypeId = $leaveType->id;
        }

        $leaveRequest->leave_type_id = $leaveTypeId;
        $leaveRequest->from_date = $validated['from_date'];
        $leaveRequest->to_date = $validated['to_date'];
        $leaveRequest->days = $days;
        $leaveRequest->reason = $validated['reason'];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-attachments', 'public');
            $leaveRequest->attachment = $path;
        }

        $leaveRequest->save();

        return $this->success([
            'leave_request' => $leaveRequest->fresh()->load('leaveType'),
        ], 'Leave request updated successfully.');
    }

    public function showLeaveRequest(string $uuid, string $childUuid, int $id): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $student = $parent->students()->where('students.uuid', $childUuid)->first();

        if (! $student) {
            return $this->notFound('Child not found for this parent.');
        }

        $leaveRequest = LeaveRequest::query()
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->with(['leaveType', 'student', 'approver:id,name'])
            ->first();

        if (! $leaveRequest) {
            return $this->notFound('Leave request not found.');
        }

        return $this->success([
            'leave_request' => [
                'id' => $leaveRequest->id,
                'student_name' => $leaveRequest->student?->name,
                'leave_type' => $leaveRequest->leaveType?->name,
                'from_date' => $leaveRequest->from_date?->format('Y-m-d'),
                'to_date' => $leaveRequest->to_date?->format('Y-m-d'),
                'days' => $leaveRequest->days,
                'reason' => $leaveRequest->reason,
                'status' => $leaveRequest->status,
                'status_label' => $leaveRequest->status_label,
                'attachment_url' => $leaveRequest->attachment_url,
                'remarks' => $leaveRequest->remarks,
                'approved_by' => $leaveRequest->approver?->name,
                'approved_at' => $leaveRequest->approved_at?->toISOString(),
                'created_at' => $leaveRequest->created_at?->toISOString(),
            ],
        ], 'Leave request retrieved.');
    }

    public function updateParentProfile(string $uuid, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $validated = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:100',
        ])->validate();

        $parent->fill($validated);
        $parent->save();

        return $this->success(
            new ParentResource($parent->fresh()->load('user', 'students')),
            'Profile updated successfully.'
        );
    }

    public function changeParentPassword(string $uuid, Request $request): JsonResponse
    {
        $parent = Guardian::query()->where('uuid', $uuid)->with('user')->first();

        if (! $parent) {
            return $this->notFound('Parent not found.');
        }

        $user = $parent->user;

        if (! $user) {
            return $this->notFound('User account not found.');
        }

        $validated = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|string|same:new_password',
        ])->validate();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->success(message: 'Password changed successfully.');
    }
}