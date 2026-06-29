<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Http\Resources\Api\V1\StudentResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Auth\Services\LoginActivityService;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Homework\Models\Homework;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class StudentAppController extends ApiBaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly LoginActivityService $loginActivityService,
    ) {}

    private function resolveStudent(): Student
    {
        $student = request()->user()->student;

        if (! $student) {
            abort($this->notFound('Student profile not found.')->getStatusCode());
        }

        return $student;
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

        $student = $user->student;
        if (! $student) {
            return $this->error('Student profile not found.', Response::HTTP_NOT_FOUND);
        }

        $schoolId = app(SchoolContext::class)->id();
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'student-app'),
            $abilities ?: ['dashboard.view']
        );

        $this->loginActivityService->recordSuccess($request, $user);

        $student->load(['sessions.classSection.schoolClass', 'sessions.classSection.section', 'sessions.academicYear']);

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'student' => new StudentResource($student),
            'school_id' => $schoolId,
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
        $attendancePercentage = 0;
        if ($academicYear) {
            $totalAttendanceDays = Attendance::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->count();

            $presentDays = Attendance::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->whereIn('status', ['present', 'late'])
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

        return $this->success([
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'full_name' => $student->full_name,
                'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
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
            'pending_homework_count' => $pendingHomeworkCount,
            'upcoming_exams' => $upcomingExams,
            'issued_books_count' => $issuedBooksCount,
            'notifications' => [
                'unread_count' => $bellData['unread_count'],
            ],
        ], 'Student dashboard retrieved.');
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
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->attendance_date?->format('Y-m-d'),
                'status' => $a->status,
                'status_label' => $a->status_label,
                'remarks' => $a->remarks,
            ]);

        return $this->success([
            'month' => $month,
            'year' => $year,
            'total_records' => $records->count(),
            'records' => $records,
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

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        $data = $paginator->through(fn ($h) => [
            'id' => $h->id,
            'title' => $h->title,
            'description' => $h->description,
            'subject' => $h->subject ? ['id' => $h->subject->id, 'name' => $h->subject->name] : null,
            'assigned_date' => $h->assigned_date?->format('Y-m-d'),
            'due_date' => $h->due_date?->format('Y-m-d'),
            'attachment_url' => $h->attachmentUrl,
        ]);

        return $this->paginated($data, 'Homework list retrieved.');
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

        $slots = TimetableSlot::query()
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
        $session = $this->currentSession();

        if (! $session) {
            return $this->success(['exams' => []], 'No active session found.');
        }

        $query = Exam::query()
            ->where('class_section_id', $session->class_section_id)
            ->with(['subject:id,name,code']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $paginator = $query->orderBy('exam_date', 'desc')
            ->paginate($request->integer('per_page', 15));

        $data = $paginator->through(fn ($e) => [
            'id' => $e->id,
            'exam_name' => $e->exam_name,
            'exam_type' => $e->exam_type,
            'exam_date' => $e->exam_date?->format('Y-m-d'),
            'subject' => $e->subject ? ['id' => $e->subject->id, 'name' => $e->subject->name] : null,
            'maximum_marks' => $e->maximum_marks,
            'pass_marks' => $e->pass_marks,
            'status' => $e->status,
            'is_published' => $e->is_published,
        ]);

        return $this->paginated($data, 'Exams retrieved.');
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

    public function notificationsRead(Request $request): JsonResponse
    {
        $userId = request()->user()->id;
        $this->notificationService->markAllRead($userId);

        return $this->success(message: 'All notifications marked as read.');
    }
}
