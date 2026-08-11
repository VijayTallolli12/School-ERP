<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\ExamResultResource;
use App\Http\Resources\Api\V1\HomeworkResource;
use App\Http\Resources\Api\V1\StudentListResource;
use App\Http\Resources\Api\V1\StudentResource;
use App\Http\Resources\Api\V1\StudentFeeResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Auth\Services\LoginActivityService;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Exams\Models\ExamSchedule;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Homework\Models\Homework;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Settings\Services\BrandingService;
use App\Modules\Students\Exceptions\StudentLinkageException;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentDocument;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Students\Services\StudentAuthService;
use App\Modules\Timetable\Models\TimetableSlot;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class StudentAppController extends ApiBaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly LoginActivityService $loginActivityService,
        private readonly StudentAuthService $studentAuth,
        private readonly BrandingService $branding,
    ) {}

    private function resolveStudent(): Student
    {
        $user = request()->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $this->studentAuth->resolveForRequest($user)->student;
    }

    private function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::query()
            ->where('school_id', app(SchoolContext::class)->id())
            ->where('is_active', true)
            ->first();
    }

    private function currentSession(): ?StudentSession
    {
        $student = $this->resolveStudent();

        return $student->sessions()
            ->where('status', 'active')
            ->with(['classSection.schoolClass', 'classSection.section', 'academicYear'])
            ->latest()
            ->first();
    }

    // ──────────────────────────────────────────────────────────────────
    // AUTH
    // ──────────────────────────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            $this->loginActivityService->recordFailure($request, 'Invalid student credentials');
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            $this->loginActivityService->recordFailure($request, 'Inactive user');
            return $this->error('This account is not active.', Response::HTTP_FORBIDDEN);
        }

        try {
            $studentContext = $this->studentAuth->resolveForLogin($user, $request);
        } catch (StudentLinkageException $e) {
            $this->loginActivityService->recordFailure($request, $e->getMessage());

            return $this->error($e->getMessage(), $e->getStatusCode());
        }

        $schoolId = $studentContext->student->school_id;
        $school = $studentContext->school;

        if ($schoolId) {
            app(SchoolContext::class)->set($schoolId);
            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        }

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'student-app'),
            $abilities ?: ['dashboard.view']
        );

        $this->loginActivityService->recordSuccess($request, $user);

        $student = $studentContext->student;
        $session = $studentContext->session;
        $academicYear = $studentContext->academicYear;

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'role' => 'Student',
            'permissions' => $abilities,
            'user' => new UserResource($user),
            'student' => new StudentResource($student),
            'student_id' => $student->id,
            'student_uuid' => $student->uuid,
            'school_id' => $schoolId,
            'academic_year' => $academicYear
                ? ['id' => $academicYear->id, 'name' => $academicYear->name]
                : null,
            'class' => $session?->classSection?->schoolClass?->name ?? null,
            'section' => $session?->classSection?->section?->name ?? null,
            'branding' => $this->branding->forSchool($school),
        ], 'Student logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->currentAccessToken()?->delete();

        if ($user) {
            $this->loginActivityService->recordLogout($request, $user);
        }

        return $this->success(message: 'Logged out successfully.');
    }

    public function profile(): JsonResponse
    {
        $student = $this->resolveStudent();
        $student->load(['user', 'sessions.classSection.schoolClass', 'sessions.classSection.section', 'sessions.academicYear', 'guardians']);

        return $this->success([
            'user' => new UserResource($student->user),
            'student' => new StudentResource($student),
        ], 'Student profile retrieved.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();

        $validated = $request->validate([
            'phone' => ['sometimes', 'string', 'max:20'],
            'current_address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'permanent_address' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $student->update($validated);

        return $this->success([
            'student' => new StudentResource($student->fresh()->load(['sessions.classSection.schoolClass', 'sessions.classSection.section'])),
        ], 'Profile updated successfully.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return $this->success(message: 'Password changed successfully.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ────────────────────────────────────────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        $student = $this->resolveStudent();
        $session = $this->currentSession();
        $academicYear = $this->currentAcademicYear();

        $classSectionId = $session?->class_section_id;

        // Attendance percentage (current academic year)
        $totalAttendanceDays = 0;
        $presentDays = 0;
        $absentDays = 0;
        $attendancePercentage = 0;
        if ($academicYear) {
            $totalAttendanceDays = Attendance::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->count();

            $presentDays = Attendance::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->count();

            $absentDays = Attendance::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'absent')
                ->count();

            $attendancePercentage = $totalAttendanceDays > 0
                ? round(($presentDays / $totalAttendanceDays) * 100, 1)
                : 0;
        }

        // Pending homework
        $pendingHomeworkCount = 0;
        if ($classSectionId) {
            $pendingHomeworkCount = Homework::query()
                ->where('class_section_id', $classSectionId)
                ->where('due_date', '>=', now()->today())
                ->where('status', 'active')
                ->count();
        }

        // Upcoming exams
        $upcomingExams = [];
        if ($classSectionId) {
            $upcomingExams = Exam::query()
                ->where('class_section_id', $classSectionId)
                ->where('exam_date', '>=', now()->today())
                ->where('status', 'scheduled')
                ->with('subject:id,name,code')
                ->orderBy('exam_date')
                ->limit(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'exam_name' => $e->exam_name,
                    'exam_type' => $e->exam_type,
                    'exam_date' => $e->exam_date?->format('Y-m-d'),
                    'subject' => $e->subject?->name,
                ]);
        }

        // Library books currently issued
        $issuedBooksCount = BookIssue::query()
            ->where('issueable_type', Student::class)
            ->where('issueable_id', $student->id)
            ->where('status', 'issued')
            ->count();

        // Notifications unread count
        $bellData = $this->notificationService->bellData($student->user_id);

        // Fees summary (mobile app contract)
        $feesSummary = $this->feesSummary($student, $academicYear);

        // Exam results summary (mobile app contract)
        $examResultsSummary = $this->examResultsSummary($student, $academicYear);

        // Leave summary (mobile app contract)
        $leaveSummary = $this->leaveSummary($student);

        return $this->success([
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'full_name' => $student->full_name,
                'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
            ],
            'students' => [
                new StudentListResource($student->loadMissing(['sessions.classSection.schoolClass', 'sessions.classSection.section'])),
            ],
            'current_session' => $session ? [
                'class' => $session->classSection?->schoolClass?->name ?? '',
                'section' => $session->classSection?->section?->name ?? '',
                'roll_no' => $session->roll_no,
                'academic_year' => $session->academicYear?->name ?? '',
            ] : null,
            'attendance' => [
                'total_days' => $totalAttendanceDays,
                'present_days' => $presentDays,
                'percentage' => $attendancePercentage,
            ],
            'attendance_summary' => [
                'present' => $presentDays,
                'absent' => $absentDays,
                'total' => $totalAttendanceDays,
                'percentage' => $attendancePercentage,
            ],
            'fees_summary' => $feesSummary,
            'exam_results_summary' => $examResultsSummary,
            'leave_summary' => $leaveSummary,
            'pending_homework_count' => $pendingHomeworkCount,
            'upcoming_exams' => $upcomingExams,
            'issued_books_count' => $issuedBooksCount,
            'notifications' => [
                'unread_count' => $bellData['unread_count'],
            ],
            'recent_notifications' => $bellData['notifications'] ?? [],
        ], 'Student dashboard retrieved.');
    }

    private function feesSummary(Student $student, ?AcademicYear $academicYear): array
    {
        $query = StudentFee::with(['items' => function ($q) {
            $q->withSum(['paymentItems as paid_sum' => fn ($sq) => $sq->whereHas('feePayment')], 'amount');
        }])->where('student_id', $student->id);

        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }

        $studentFees = $query->get();

        $total = 0;
        $paid = 0;
        foreach ($studentFees as $fee) {
            foreach ($fee->items as $item) {
                $total += (float) $item->amount;
                $paid += (float) ($item->paid_sum ?? 0);
            }
        }

        return [
            'total' => $total,
            'paid' => $paid,
            'pending' => max(0, $total - $paid),
        ];
    }

    private function examResultsSummary(Student $student, ?AcademicYear $academicYear): array
    {
        if (! $academicYear) {
            return ['average' => 0, 'subjects' => 0, 'total_marks' => 0, 'obtained_marks' => 0];
        }

        $aggregate = ExamResult::query()
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->where('exam_results.student_id', $student->id)
            ->where('exams.academic_year_id', $academicYear->id)
            ->where('exams.is_published', true)
            ->selectRaw('
                SUM(exams.maximum_marks) as total_maximum_marks,
                SUM(exam_results.marks_obtained) as total_obtained_marks
            ')
            ->first();

        $totalMarks = (float) ($aggregate->total_maximum_marks ?? 0);
        $obtainedMarks = (float) ($aggregate->total_obtained_marks ?? 0);
        $average = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        $subjects = 0;
        if ($session = $this->currentSession()) {
            $subjects = \App\Modules\Academics\Models\ClassSubject::query()
                ->where('class_id', $session->classSection?->class_id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active')
                ->count();
            if ($subjects <= 0 && $session->class_section_id) {
                $subjects = TimetableSlot::where('class_section_id', $session->class_section_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->distinct('subject_id')
                    ->count('subject_id');
            }
        }

        return [
            'average' => $average,
            'subjects' => $subjects,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
        ];
    }

    private function leaveSummary(Student $student): array
    {
        $requests = LeaveRequest::query()
            ->where('student_id', $student->id)
            ->get();

        return [
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
            'total' => $requests->count(),
        ];
    }

    // ────────────────────────────────────────────────────────────────────────────
    // ATTENDANCE
    // ────────────────────────────────────────────────────────────────────────────

    public function attendance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();

        $validated = $request->validate([
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month = $validated['month'] ?? (int) now()->month;
        $year = $validated['year'] ?? (int) now()->year;

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['classSection.schoolClass', 'classSection.section'])
            ->orderBy('attendance_date')
            ->get();

        $statuses = Attendance::getStatuses();
        $counts = [];
        foreach ($statuses as $key => $label) {
            $counts[$key] = $records->where('status', $key)->count();
        }

        $mapped = $records->map(fn ($a) => [
            'id' => $a->id,
            'student_id' => $a->student_id,
            'date' => $a->attendance_date?->format('Y-m-d'),
            'attendance_date' => $a->attendance_date?->format('Y-m-d'),
            'status' => $a->status,
            'status_label' => $a->status_label,
            'remark' => $a->remarks,
            'remarks' => $a->remarks,
        ]);

        return $this->success([
            'student' => new StudentListResource($student),
            'month' => $month,
            'year' => $year,
            'total_records' => $records->count(),
            'summary' => [
                'total_days' => $records->count(),
                'counts' => $counts,
            ],
            'records' => $mapped,
        ], 'Attendance records retrieved.');
    }

    public function attendanceMonthly(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();

        $validated = $request->validate([
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month = $validated['month'] ?? (int) now()->month;
        $year = $validated['year'] ?? (int) now()->year;

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        $counts = [];
        foreach (Attendance::getStatuses() as $key => $label) {
            $counts[$key] = ['count' => $records->where('status', $key)->count(), 'label' => $label];
        }

        $totalDays = $records->count();
        $presentDays = $records->whereIn('status', ['present', 'late'])->count();
        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        return $this->success([
            'month' => $month,
            'year' => $year,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $records->where('status', 'absent')->count(),
            'percentage' => $percentage,
            'breakdown' => $counts,
        ], 'Monthly attendance summary retrieved.');
    }

    public function attendanceSummary(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();
        $academicYear = $this->currentAcademicYear();

        $validated = $request->validate([
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
        ]);

        $academicYearId = $validated['academic_year_id'] ?? $academicYear?->id;

        $query = Attendance::query()->where('student_id', $student->id);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $records = $query->get();

        $counts = [];
        foreach (Attendance::getStatuses() as $key => $label) {
            $counts[$key] = ['count' => $records->where('status', $key)->count(), 'label' => $label];
        }

        $totalDays = $records->count();
        $presentDays = $records->whereIn('status', ['present', 'late'])->count();
        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        return $this->success([
            'academic_year_id' => $academicYearId,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'percentage' => $percentage,
            'breakdown' => $counts,
        ], 'Attendance summary retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // HOMEWORK
    // ────────────────────────────────────────────────────────────────────────────

    public function homeworkIndex(Request $request): JsonResponse
    {
        $session = $this->currentSession();

        if (! $session) {
            return $this->success(['homework' => []], 'No active session found.');
        }

        $query = Homework::query()
            ->where('class_section_id', $session->class_section_id)
            ->where('status', 'active')
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        $homework = $query->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'student' => new StudentListResource($this->resolveStudent()->loadMissing(['sessions.classSection.schoolClass', 'sessions.classSection.section'])),
            'homework' => HomeworkResource::collection($homework),
        ], 'Homework list retrieved.');
    }

    public function homeworkShow(int $id): JsonResponse
    {
        $session = $this->currentSession();

        if (! $session) {
            return $this->error('No active session found.', Response::HTTP_NOT_FOUND);
        }

        $homework = Homework::query()
            ->where('class_section_id', $session->class_section_id)
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section'])
            ->findOrFail($id);

        return $this->success([
            'id' => $homework->id,
            'title' => $homework->title,
            'description' => $homework->description,
            'subject' => $homework->subject ? ['id' => $homework->subject->id, 'name' => $homework->subject->name] : null,
            'class_section' => $homework->classSection ? [
                'class' => $homework->classSection->schoolClass?->name ?? '',
                'section' => $homework->classSection->section?->name ?? '',
            ] : null,
            'assigned_date' => $homework->assigned_date?->format('Y-m-d'),
            'due_date' => $homework->due_date?->format('Y-m-d'),
            'attachment_url' => $homework->attachmentUrl,
            'status' => $homework->status,
            'created_at' => $homework->created_at?->toISOString(),
        ], 'Homework detail retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // TIMETABLE
    // ────────────────────────────────────────────────────────────────────────────

    public function timetable(): JsonResponse
    {
        $session = $this->currentSession();

        if (! $session || ! $session->class_section_id) {
            return $this->success(['timetable' => []], 'No active session found.');
        }

        $academicYear = $this->currentAcademicYear();

        // class_section_id is globally unique, so the tenant scope is redundant
        // here and would drop legacy slots whose school_id was not persisted.
        $slots = TimetableSlot::query()
            ->withoutGlobalScope('school')
            ->where('class_section_id', $session->class_section_id)
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with(['subject:id,name,code', 'teacher.user:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($daySlots, $day) => [
                'day_of_week' => (int) $day,
                'day_name' => TimetableSlot::days()[(int) $day] ?? 'Unknown',
                'slots' => $daySlots->map(fn ($s) => [
                    'id' => $s->id,
                    'period_label' => $s->period_label,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'subject' => $s->subject ? ['id' => $s->subject->id, 'name' => $s->subject->name, 'code' => $s->subject->code] : null,
                    'teacher' => $s->teacher?->user ? ['id' => $s->teacher->user->id, 'name' => $s->teacher->user->name] : null,
                    'room' => $s->room,
                ]),
            ])
            ->values();

        return $this->success([
            'timetable' => $slots,
        ], 'Timetable retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // EXAMS
    // ────────────────────────────────────────────────────────────────────────────

    public function examsIndex(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();
        $academicYear = $this->currentAcademicYear();

        $validated = $request->validate([
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
        ]);

        $academicYearId = $validated['academic_year_id'] ?? $academicYear?->id;

        $results = ExamResult::query()
            ->where('student_id', $student->id)
            ->when($academicYearId, fn ($q) => $q->whereHas('exam', fn ($eq) => $eq->where('academic_year_id', $academicYearId)))
            ->whereHas('exam', fn ($eq) => $eq->where('is_published', true))
            ->with(['exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn ($r) => $r->exam->academic_year_id ?? 'unknown');

        return $this->success([
            'student' => new StudentListResource($student->loadMissing(['sessions.classSection.schoolClass', 'sessions.classSection.section'])),
            'results_by_academic_year' => $results->map(fn ($group) => ExamResultResource::collection($group)),
        ], 'Exam results retrieved.');
    }

    public function results(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();
        $academicYear = $this->currentAcademicYear();

        $validated = $request->validate([
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
        ]);

        $academicYearId = $validated['academic_year_id'] ?? $academicYear?->id;

        $results = ExamResult::query()
            ->where('student_id', $student->id)
            ->when($academicYearId, fn ($q) => $q->whereHas('exam', fn ($eq) => $eq->where('academic_year_id', $academicYearId)))
            ->whereHas('exam', fn ($eq) => $eq->where('is_published', true))
            ->with(['exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn ($r) => $r->exam->academic_year_id ?? 'unknown');

        return $this->success([
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'full_name' => $student->full_name,
            ],
            'results_by_academic_year' => $results->map(fn ($group, $yearId) => [
                'academic_year_id' => (int) $yearId,
                'results' => $group->map(fn ($r) => [
                    'id' => $r->id,
                    'exam_name' => $r->exam->exam_name,
                    'exam_type' => $r->exam->exam_type,
                    'exam_date' => $r->exam->exam_date?->format('Y-m-d'),
                    'subject' => $r->exam->subject?->name,
                    'maximum_marks' => $r->exam->maximum_marks,
                    'pass_marks' => $r->exam->pass_marks,
                    'marks_obtained' => $r->marks_obtained,
                    'grade' => $r->grade,
                    'status' => $r->status,
                    'status_label' => $r->status_label,
                    'remarks' => $r->remarks,
                ]),
            ])->values(),
        ], 'Exam results retrieved.');
    }

    public function reportCard(): JsonResponse
    {
        $student = $this->resolveStudent();
        $session = $this->currentSession();
        $academicYear = $this->currentAcademicYear();

        if (! $session) {
            return $this->error('No active session found.', Response::HTTP_NOT_FOUND);
        }

        $results = ExamResult::query()
            ->where('student_id', $student->id)
            ->whereHas('exam', fn ($q) => $q
                ->where('class_section_id', $session->class_section_id)
                ->when($academicYear, fn ($aq) => $aq->where('academic_year_id', $academicYear->id))
            )
            ->with(['exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->get();

        $grouped = $results->groupBy(fn ($r) => $r->exam->exam_type ?? 'Other');

        return $this->success([
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'full_name' => $student->full_name,
            ],
            'class_section' => $session ? [
                'class' => $session->classSection?->schoolClass?->name ?? '',
                'section' => $session->classSection?->section?->name ?? '',
                'roll_no' => $session->roll_no,
            ] : null,
            'academic_year' => $academicYear?->name,
            'results_by_type' => $grouped->map(fn ($exams, $type) => [
                'exam_type' => $type,
                'results' => $exams->map(fn ($r) => [
                    'exam_name' => $r->exam->exam_name,
                    'exam_date' => $r->exam->exam_date?->format('Y-m-d'),
                    'subject' => $r->exam->subject?->name,
                    'maximum_marks' => $r->exam->maximum_marks,
                    'pass_marks' => $r->exam->pass_marks,
                    'marks_obtained' => $r->marks_obtained,
                    'grade' => $r->grade,
                    'status' => $r->status,
                ]),
            ])->values(),
        ], 'Report card retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // LIBRARY
    // ────────────────────────────────────────────────────────────────────────────

    public function libraryBooks(): JsonResponse
    {
        $student = $this->resolveStudent();

        $issues = BookIssue::query()
            ->where('issueable_type', Student::class)
            ->where('issueable_id', $student->id)
            ->where('status', 'issued')
            ->with('book:id,title,isbn,author_id', 'book.author:id,name')
            ->latest()
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'book' => [
                    'id' => $i->book?->id,
                    'title' => $i->book?->title,
                    'isbn' => $i->book?->isbn,
                    'author' => $i->book?->author?->name,
                ],
                'issue_date' => $i->issue_date?->format('Y-m-d'),
                'due_date' => $i->due_date?->format('Y-m-d'),
                'fine_amount' => $i->fine_amount,
                'fine_paid' => $i->fine_paid,
                'notes' => $i->notes,
            ]);

        return $this->success([
            'total_issued' => $issues->count(),
            'books' => $issues,
        ], 'Currently issued books retrieved.');
    }

    public function libraryHistory(): JsonResponse
    {
        $student = $this->resolveStudent();

        $issues = BookIssue::query()
            ->where('issueable_type', Student::class)
            ->where('issueable_id', $student->id)
            ->with('book:id,title,isbn,author_id', 'book.author:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(request()->integer('per_page', 15));

        $data = $issues->through(fn ($i) => [
            'id' => $i->id,
            'book' => [
                'id' => $i->book?->id,
                'title' => $i->book?->title,
                'isbn' => $i->book?->isbn,
                'author' => $i->book?->author?->name,
            ],
            'issue_date' => $i->issue_date?->format('Y-m-d'),
            'due_date' => $i->due_date?->format('Y-m-d'),
            'return_date' => $i->return_date?->format('Y-m-d'),
            'status' => $i->status,
            'fine_amount' => $i->fine_amount,
            'fine_paid' => $i->fine_paid,
        ]);

        return $this->paginated($data, 'Library history retrieved.');
    }

    public function libraryFines(): JsonResponse
    {
        $student = $this->resolveStudent();

        $fines = BookIssue::query()
            ->where('issueable_type', Student::class)
            ->where('issueable_id', $student->id)
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->with('book:id,title,isbn')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'book' => [
                    'id' => $i->book?->id,
                    'title' => $i->book?->title,
                ],
                'issue_date' => $i->issue_date?->format('Y-m-d'),
                'due_date' => $i->due_date?->format('Y-m-d'),
                'return_date' => $i->return_date?->format('Y-m-d'),
                'fine_amount' => $i->fine_amount,
                'fine_paid' => $i->fine_paid,
                'notes' => $i->notes,
            ]);

        $totalFine = $fines->sum('fine_amount');

        return $this->success([
            'total_outstanding_fine' => round($totalFine, 2),
            'total_items' => $fines->count(),
            'fines' => $fines,
        ], 'Outstanding library fines retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS
    // ────────────────────────────────────────────────────────────────────────────

    public function notificationsIndex(): JsonResponse
    {
        $userId = request()->user()->id;
        $bellData = $this->notificationService->bellData($userId);

        return $this->success($bellData, 'Notifications retrieved.');
    }

    public function notificationsReadAll(Request $request): JsonResponse
    {
        $userId = request()->user()->id;
        $this->notificationService->markAllRead($userId);

        return $this->success(message: 'All notifications marked as read.');
    }

    public function notificationsUnread(Request $request): JsonResponse
    {
        $userId = request()->user()->id;
        $bellData = $this->notificationService->bellData($userId);

        return $this->success([
            'unread_count' => $bellData['unread_count'],
        ], 'Unread count retrieved.');
    }

    public function notificationShow(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $notification = Notification::query()
            ->where('status', 'sent')
            ->whereHas('users', fn ($q) => $q->where('notification_user.user_id', $userId))
            ->with(['users' => fn ($q) => $q->where('notification_user.user_id', $userId)])
            ->find($id);

        if (! $notification) {
            return $this->error('Notification not found.', Response::HTTP_NOT_FOUND);
        }

        $pivot = $notification->users->first()?->pivot;

        return $this->success([
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'type_label' => $notification->type_label,
            'priority' => $notification->priority,
            'is_read' => (bool) ($pivot?->is_read ?? false),
            'sent_at' => $notification->sent_at?->toISOString(),
            'read_at' => $pivot?->read_at,
            'created_at' => $notification->created_at?->toISOString(),
        ], 'Notification detail retrieved.');
    }

    public function notificationRead(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $notification = Notification::query()
            ->where('status', 'sent')
            ->whereHas('users', fn ($q) => $q->where('notification_user.user_id', $userId))
            ->find($id);

        if (! $notification) {
            return $this->error('Notification not found.', Response::HTTP_NOT_FOUND);
        }

        $this->notificationService->markRead($notification, $userId);

        return $this->success(message: 'Notification marked as read.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // FEES
    // ────────────────────────────────────────────────────────────────────────────

    public function fees(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();
        $academicYear = $this->currentAcademicYear();

        $query = StudentFee::query()
            ->where('student_id', $student->id)
            ->with(['academicYear', 'items.feeCategory', 'items.paymentItems']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        } elseif ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }

        $fees = $query->orderByDesc('id')->get();

        return $this->success(
            StudentFeeResource::collection($fees),
            'Student fees retrieved.'
        );
    }

    // ────────────────────────────────────────────────────────────────────────────
    // EXAM SCHEDULE
    // ────────────────────────────────────────────────────────────────────────────

    public function examSchedule(): JsonResponse
    {
        $session = $this->currentSession();

        if (! $session) {
            return $this->success(['schedules' => []], 'No active session found.');
        }

        $academicYear = $this->currentAcademicYear();

        $scheduledExams = Exam::query()
            ->where('class_section_id', $session->class_section_id)
            ->where('status', 'scheduled')
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with('subject:id,name,code')
            ->orderBy('exam_date')
            ->get();

        $schedules = collect();

        // Optional per-subject schedule rows carry start/end time + room.
        $scheduleRows = ExamSchedule::query()
            ->whereIn('exam_id', $scheduledExams->pluck('id'))
            ->with('subject:id,name,code')
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('exam_id');

        foreach ($scheduledExams as $exam) {
            $rows = $scheduleRows->get($exam->id);

            if ($rows && $rows->count() > 0) {
                foreach ($rows as $s) {
                    $schedules->push([
                        'id' => $s->id,
                        'exam_name' => $exam->exam_name,
                        'subject_name' => $s->subject?->name,
                        'exam_date' => $s->exam_date?->format('Y-m-d'),
                        'start_time' => $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('H:i') : null,
                        'end_time' => $s->end_time ? \Carbon\Carbon::parse($s->end_time)->format('H:i') : null,
                        'room' => $s->room,
                        'maximum_marks' => $s->maximum_marks,
                        'pass_marks' => $s->pass_marks,
                    ]);
                }
            } else {
                $schedules->push([
                    'id' => $exam->id,
                    'exam_name' => $exam->exam_name,
                    'subject_name' => $exam->subject?->name,
                    'exam_date' => $exam->exam_date?->format('Y-m-d'),
                    'start_time' => null,
                    'end_time' => null,
                    'room' => null,
                    'maximum_marks' => $exam->maximum_marks,
                    'pass_marks' => $exam->pass_marks,
                ]);
            }
        }

        return $this->success([
            'schedules' => $schedules->values(),
        ], 'Exam schedule retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // ASSIGNMENTS
    // ────────────────────────────────────────────────────────────────────────────

    public function assignments(): JsonResponse
    {
        $session = $this->currentSession();

        if (! $session) {
            return $this->success(['assignments' => []], 'No active session found.');
        }

        $assignments = Homework::query()
            ->where('class_section_id', $session->class_section_id)
            ->where('status', 'active')
            ->with(['subject:id,name,code'])
            ->orderByDesc('assigned_date')
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'title' => $h->title,
                'description' => $h->description,
                'subject_name' => $h->subject?->name,
                'assigned_date' => $h->assigned_date?->format('Y-m-d'),
                'due_date' => $h->due_date?->format('Y-m-d'),
                'status' => $h->status,
                'attachment_url' => $h->attachmentUrl,
            ]);

        return $this->success([
            'assignments' => $assignments,
        ], 'Assignments retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // ACADEMIC CALENDAR
    // ────────────────────────────────────────────────────────────────────────────

    public function calendar(Request $request): JsonResponse
    {
        $month = $request->integer('month', (int) now()->month);
        $year = $request->integer('year', (int) now()->year);
        $type = $request->input('type');

        $query = AcademicCalendar::query()
            ->published()
            ->where(function ($q) {
                $q->where('audience', 'all')
                    ->orWhere('audience', 'students');
            })
            ->byMonth($year, $month)
            ->orderBy('start_date');

        if ($type) {
            $query->where('event_type', $type);
        }

        $events = $query->get()->map(fn ($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'event_type' => $e->event_type,
            'event_type_label' => $e->event_type_label,
            'start_date' => $e->start_date?->format('Y-m-d'),
            'end_date' => $e->end_date?->format('Y-m-d'),
            'is_published' => $e->is_published,
            'location' => $e->location,
            'audience' => $e->audience,
        ]);

        return $this->success([
            'month' => $month,
            'year' => $year,
            'events' => $events,
        ], 'Academic calendar retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // DOCUMENTS
    // ────────────────────────────────────────────────────────────────────────────

    public function documents(): JsonResponse
    {
        $student = $this->resolveStudent();

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
        ], 'Documents retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // TRANSPORT
    // ────────────────────────────────────────────────────────────────────────────

    public function transport(): JsonResponse
    {
        $student = $this->resolveStudent();

        $assignment = TransportAssignment::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['route', 'vehicle', 'stop', 'vehicle.driver'])
            ->first();

        if (! $assignment) {
            return $this->success([
                'transport' => null,
                'stops' => [],
            ], 'No transport assigned.');
        }

        $route = $assignment->route;
        $vehicle = $assignment->vehicle;
        $driver = $vehicle?->driver;
        $stops = $route?->stops ?? collect();

        $studentStop = $stops->firstWhere('id', $assignment->route_stop_id);
        $pickupStop = $studentStop ?? $stops->first();
        $dropStop = $stops->last();

        $formatTime = function (?string $time): ?string {
            if ($time === null) {
                return null;
            }
            try {
                return \Carbon\Carbon::parse($time)->format('H:i');
            } catch (\Exception $e) {
                return null;
            }
        };

        return $this->success([
            'transport' => [
                'vehicle_number' => $vehicle?->vehicle_number ?? null,
                'vehicle_name' => $vehicle?->vehicle_name ?? null,
                'vehicle_type' => $vehicle?->vehicle_type ?? null,
                'driver_name' => $driver?->name ?? null,
                'driver_mobile' => $driver?->mobile ?? null,
                'driver_license' => $driver?->license_number ?? null,
                'route_name' => $route?->route_name ?? null,
                'route_start' => $route?->start_point ?? null,
                'route_end' => $route?->end_point ?? null,
                'pickup_stop' => $pickupStop?->stop_name ?? $assignment->pickup_point ?? null,
                'drop_stop' => $dropStop?->stop_name ?? null,
                'pickup_time' => $formatTime($pickupStop?->pickup_time),
                'drop_time' => $formatTime($dropStop?->drop_time),
                'status' => $assignment->status,
                'monthly_fee' => $assignment->monthly_fee,
            ],
            'stops' => $stops->map(fn (RouteStop $s) => [
                'id' => $s->id,
                'stop_name' => $s->stop_name,
                'pickup_time' => $formatTime($s->pickup_time),
                'drop_time' => $formatTime($s->drop_time),
                'sequence' => $s->sequence,
                'is_student_stop' => $s->id === $assignment->route_stop_id,
            ])->values()->all(),
        ], 'Transport details retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // LEAVE REQUESTS
    // ────────────────────────────────────────────────────────────────────────────

    public function leaveRequests(): JsonResponse
    {
        $student = $this->resolveStudent();

        $requests = LeaveRequest::query()
            ->where('student_id', $student->id)
            ->with(['leaveType', 'student'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (LeaveRequest $lr) => $this->formatLeaveRequest($lr, $student));

        return $this->success([
            'leave_requests' => $requests,
        ], 'Leave requests retrieved.');
    }

    public function showLeaveRequest(int $id): JsonResponse
    {
        $student = $this->resolveStudent();

        $leaveRequest = LeaveRequest::query()
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->with(['leaveType', 'student', 'approver:id,name'])
            ->first();

        if (! $leaveRequest) {
            return $this->notFound('Leave request not found.');
        }

        return $this->success([
            'leave_request' => $this->formatLeaveRequest($leaveRequest, $student),
        ], 'Leave request retrieved.');
    }

    public function storeLeaveRequest(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();

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
        $leaveRequest->user_id = $student->user_id;
        $leaveRequest->student_id = $student->id;
        $leaveRequest->leave_type_id = $leaveTypeId;
        $leaveRequest->from_date = $validated['from_date'];
        $leaveRequest->to_date = $validated['to_date'];
        $leaveRequest->days = $days;
        $leaveRequest->reason = $validated['reason'];
        $leaveRequest->status = 'pending';
        $leaveRequest->created_by = $student->user_id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-attachments', 'public');
            $leaveRequest->attachment = $path;
        }

        $leaveRequest->save();

        return $this->created([
            'leave_request' => $this->formatLeaveRequest($leaveRequest->fresh()->load('leaveType', 'student', 'approver:id,name'), $student),
        ], 'Leave request submitted successfully.');
    }

    public function updateLeaveRequest(int $id, Request $request): JsonResponse
    {
        $student = $this->resolveStudent();

        $leaveRequest = LeaveRequest::query()
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (! $leaveRequest) {
            return $this->notFound('Leave request not found.');
        }

        if ($leaveRequest->status !== 'pending') {
            return $this->error('Only pending leave requests can be edited.', Response::HTTP_UNPROCESSABLE_ENTITY);
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
            'leave_request' => $this->formatLeaveRequest($leaveRequest->fresh()->load('leaveType', 'student', 'approver:id,name'), $student),
        ], 'Leave request updated successfully.');
    }

    private function formatLeaveRequest(LeaveRequest $lr, Student $student): array
    {
        return [
            'id' => $lr->id,
            'student_id' => $lr->student_id,
            'student_name' => $student->full_name,
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
            'approved_by' => $lr->approver?->name,
            'approved_at' => $lr->approved_at?->toISOString(),
            'created_at' => $lr->created_at?->toISOString(),
        ];
    }

    // ────────────────────────────────────────────────────────────────────────────
    // CIRCULARS / ANNOUNCEMENTS
    // ────────────────────────────────────────────────────────────────────────────

    public function circulars(Request $request): JsonResponse
    {
        $user = request()->user();

        $paginator = Notification::query()
            ->whereIn('target_type', ['students', 'all'])
            ->where('type', 'announcement')
            ->where('status', 'sent')
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        $paginator->getCollection()->transform(function (Notification $notification) {
            return $this->formatCircular($notification);
        });

        return $this->paginated($paginator, 'Circulars retrieved.');
    }

    public function showCircular(int $id): JsonResponse
    {
        $notification = Notification::query()
            ->whereIn('target_type', ['students', 'all'])
            ->where('type', 'announcement')
            ->where('id', $id)
            ->with('creator:id,name')
            ->first();

        if (! $notification) {
            return $this->notFound('Circular not found.');
        }

        return $this->success(
            $this->formatCircular($notification),
            'Circular retrieved.'
        );
    }

    public function markCircularRead(int $id): JsonResponse
    {
        $user = request()->user();

        $notification = Notification::query()
            ->whereIn('target_type', ['students', 'all'])
            ->where('type', 'announcement')
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return $this->notFound('Circular not found.');
        }

        $notification->users()->syncWithoutDetaching([
            $user->id => [
                'is_read' => true,
                'read_at' => now(),
                'delivery_status' => 'delivered',
            ],
        ]);

        $notification->load('creator:id,name');

        return $this->success(
            $this->formatCircular($notification),
            'Circular marked as read.'
        );
    }

    private function formatCircular(Notification $notification): array
    {
        $userId = request()->user()?->id;
        $pivot = null;

        if ($userId) {
            $pivot = $notification->users()
                ->where('user_id', $userId)
                ->first()?->pivot;
        }

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
}
