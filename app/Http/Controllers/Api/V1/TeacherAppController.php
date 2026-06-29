<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Events\AttendanceMarked;
use App\Http\Resources\Api\V1\TeacherResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Auth\Services\LoginActivityService;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Services\ExamService;
use App\Modules\Homework\Models\Homework;
use App\Modules\Homework\Services\HomeworkService;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Leave\Services\LeaveService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Students\Models\StudentSession;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class TeacherAppController extends ApiBaseController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly HomeworkService $homeworkService,
        private readonly ExamService $examService,
        private readonly LeaveService $leaveService,
        private readonly NotificationService $notificationService,
        private readonly LoginActivityService $loginActivityService,
    ) {}

    private function resolveTeacher(): Teacher
    {
        $teacher = request()->user()->teacher;

        if (! $teacher) {
            abort($this->notFound('Teacher profile not found.')->getStatusCode());
        }

        return $teacher;
    }

    private function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::query()
            ->where('school_id', app(SchoolContext::class)->id())
            ->where('is_active', true)
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
            $this->loginActivityService->recordFailure($request, 'Invalid teacher credentials');
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            $this->loginActivityService->recordFailure($request, 'Inactive user');
            return $this->error('This account is not active.', Response::HTTP_FORBIDDEN);
        }

        $schoolId = $user->current_school_id ?? $user->schools()->first()?->id;
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        if (! $user->hasRole('Teacher')) {
            $this->loginActivityService->recordFailure($request, 'Non-teacher login attempt');
            return $this->error('Only teacher accounts can use this endpoint.', Response::HTTP_FORBIDDEN);
        }

        $teacher = $user->teacher;
        if (! $teacher) {
            return $this->error('Teacher profile not found.', Response::HTTP_NOT_FOUND);
        }

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'teacher-app'),
            $abilities ?: ['dashboard.view']
        );

        $this->loginActivityService->recordSuccess($request, $user);

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'teacher' => new TeacherResource($teacher->load(['classSections.schoolClass', 'classSections.section', 'subjects'])),
            'school_id' => $schoolId,
        ], 'Teacher logged in successfully.');
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
        $teacher = $this->resolveTeacher();
        $teacher->load(['user', 'classSections.schoolClass', 'classSections.section', 'subjects']);

        return $this->success([
            'user' => new UserResource($teacher->user),
            'teacher' => new TeacherResource($teacher),
        ], 'Teacher profile retrieved.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();

        $validated = $request->validate([
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'qualification' => ['sometimes', 'nullable', 'string', 'max:200'],
        ]);

        $teacher->update($validated);

        return $this->success([
            'teacher' => new TeacherResource($teacher->fresh()->load(['user', 'classSections.schoolClass', 'classSections.section'])),
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
        $teacher = $this->resolveTeacher();
        $teacherId = $teacher->id;
        $academicYear = $this->currentAcademicYear();
        $today = now()->dayOfWeekIso; // 1=Mon .. 6=Sat

        // Today's classes (timetable slots for today)
        $todayClasses = TimetableSlot::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $today)
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section'])
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'subject' => $s->subject?->name,
                'class_section' => ($s->classSection?->schoolClass?->name ?? '') . ' - ' . ($s->classSection?->section?->name ?? ''),
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'room' => $s->room,
            ]);

        // Today's attendance status (teacher's own)
        $myAttendance = \App\Modules\Teachers\Models\TeacherAttendance::query()
            ->where('teacher_id', $teacherId)
            ->whereDate('attendance_date', now()->today())
            ->first();

        // Pending homework count
        $pendingHomeworkCount = Homework::query()
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->where('due_date', '>=', now()->today())
            ->count();

        // Upcoming exams
        $upcomingExams = Exam::query()
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->where('exam_date', '>=', now()->today())
            ->where('status', 'scheduled')
            ->orderBy('exam_date')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'exam_name' => $e->exam_name,
                'exam_type' => $e->exam_type,
                'exam_date' => $e->exam_date?->format('Y-m-d'),
                'subject' => $e->subject?->name,
                'class_section' => ($e->classSection?->schoolClass?->name ?? '') . ' - ' . ($e->classSection?->section?->name ?? ''),
            ]);

        // Notifications unread count
        $bellData = $this->notificationService->bellData($teacher->user_id);

        return $this->success([
            'teacher' => [
                'id' => $teacher->id,
                'uuid' => $teacher->uuid,
                'full_name' => $teacher->full_name,
                'photo_url' => $teacher->photo_path ? asset('storage/' . $teacher->photo_path) : null,
            ],
            'today_classes' => $todayClasses,
            'my_attendance_today' => $myAttendance ? [
                'status' => $myAttendance->status,
                'status_label' => $myAttendance->status_label ?? ucfirst($myAttendance->status),
                'remarks' => $myAttendance->remarks,
            ] : null,
            'pending_homework_count' => $pendingHomeworkCount,
            'upcoming_exams' => $upcomingExams,
            'notifications' => [
                'unread_count' => $bellData['unread_count'],
            ],
        ], 'Teacher dashboard retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // TIMETABLE
    // ────────────────────────────────────────────────────────────────────────────

    public function timetable(): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $academicYear = $this->currentAcademicYear();

        $slots = TimetableSlot::query()
            ->where('teacher_id', $teacher->id)
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section'])
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
                    'class_section' => $s->classSection ? [
                        'id' => $s->classSection->id,
                        'class' => $s->classSection->schoolClass?->name ?? '',
                        'section' => $s->classSection->section?->name ?? '',
                    ] : null,
                    'room' => $s->room,
                ]),
            ])
            ->values();

        return $this->success([
            'timetable' => $slots,
            'classes' => $teacher->classSections->map(fn ($cs) => [
                'id' => $cs->id,
                'class' => $cs->schoolClass?->name ?? '',
                'section' => $cs->section?->name ?? '',
                'is_class_teacher' => (bool) ($cs->pivot->is_class_teacher ?? false),
            ]),
        ], 'Teacher timetable retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // ATTENDANCE
    // ────────────────────────────────────────────────────────────────────────────

    public function attendanceClasses(): JsonResponse
    {
        $teacher = $this->resolveTeacher();

        $classes = $teacher->classSections()->with(['schoolClass', 'section'])->get()->map(fn ($cs) => [
            'id' => $cs->id,
            'class' => $cs->schoolClass?->name ?? '',
            'section' => $cs->section?->name ?? '',
            'is_class_teacher' => (bool) ($cs->pivot->is_class_teacher ?? false),
            'subject_count' => $teacher->subjects->count(),
        ]);

        return $this->success([
            'classes' => $classes,
        ], 'Attendance classes retrieved.');
    }

    public function attendanceStudents(string $classSectionId, Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $academicYear = $this->currentAcademicYear();

        $cs = ClassSection::query()->findOrFail($classSectionId);

        // Verify teacher owns this class
        if (! $teacher->classSections->contains('id', (int) $classSectionId)) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        $date = $request->input('date', now()->toDateString());

        $students = StudentSession::query()
            ->where('class_section_id', $classSectionId)
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with('student.user')
            ->where('status', 'active')
            ->get()
            ->map(function ($session) use ($date) {
                $attendance = Attendance::query()
                    ->where('student_id', $session->student_id)
                    ->where('attendance_date', $date)
                    ->first();

                return [
                    'student_id' => $session->student->id,
                    'uuid' => $session->student->uuid,
                    'admission_no' => $session->student->admission_no,
                    'full_name' => $session->student->full_name,
                    'roll_no' => $session->roll_no,
                    'photo_url' => $session->student->photo_path ? asset('storage/' . $session->student->photo_path) : null,
                    'attendance' => $attendance ? [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'status_label' => ucfirst($attendance->status),
                        'remarks' => $attendance->remarks,
                    ] : null,
                ];
            });

        return $this->success([
            'class_section' => [
                'id' => $cs->id,
                'class' => $cs->schoolClass?->name ?? '',
                'section' => $cs->section?->name ?? '',
            ],
            'date' => $date,
            'total_students' => $students->count(),
            'students' => $students,
        ], 'Students retrieved.');
    }

    public function markAttendance(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $academicYear = $this->currentAcademicYear();

        $validated = $request->validate([
            'class_section_id' => ['required', 'integer', 'exists:class_section,id'],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'students' => ['required', 'array', 'min:1'],
            'students.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'students.*.status' => ['required', 'string', 'in:present,absent,late,half_day,excused'],
            'students.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify teacher owns this class
        if (! $teacher->classSections->contains('id', (int) $validated['class_section_id'])) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        if (! $academicYear) {
            return $this->error('No active academic year found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $studentIds = collect($validated['students'])->pluck('student_id');
        $students = \App\Modules\Students\Models\Student::query()
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');
        $studentNames = $students->map(fn ($s) => $s->full_name);

        $results = [];
        foreach ($validated['students'] as $studentData) {
            $attendance = $this->attendanceService->markAttendance([
                'student_id' => $studentData['student_id'],
                'class_section_id' => $validated['class_section_id'],
                'academic_year_id' => $academicYear->id,
                'attendance_date' => $validated['attendance_date'],
                'status' => $studentData['status'],
                'remarks' => $studentData['remarks'] ?? null,
                'marked_by' => $teacher->user_id,
            ]);

            AttendanceMarked::dispatch(
                schoolId: app(SchoolContext::class)->id(),
                studentId: $studentData['student_id'],
                status: $studentData['status'],
                date: $validated['attendance_date'],
                studentName: $studentNames[$studentData['student_id']] ?? "Student #{$studentData['student_id']}",
                markedAt: now()->format('h:i A'),
            );

            $results[] = [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'status' => $attendance->status,
            ];
        }

        return $this->success([
            'attendance_date' => $validated['attendance_date'],
            'class_section_id' => $validated['class_section_id'],
            'marked_count' => count($results),
            'records' => $results,
        ], 'Attendance marked successfully.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // HOMEWORK
    // ────────────────────────────────────────────────────────────────────────────

    public function homeworkIndex(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $classSectionIds = $teacher->classSections->pluck('id');

        $query = Homework::query()
            ->whereIn('class_section_id', $classSectionIds)
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section']);

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->integer('class_section_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        $data = $paginator->through(fn ($h) => [
            'id' => $h->id,
            'title' => $h->title,
            'description' => $h->description,
            'subject' => $h->subject ? ['id' => $h->subject->id, 'name' => $h->subject->name] : null,
            'class_section' => $h->classSection ? [
                'class' => $h->classSection->schoolClass?->name ?? '',
                'section' => $h->classSection->section?->name ?? '',
            ] : null,
            'assigned_date' => $h->assigned_date?->format('Y-m-d'),
            'due_date' => $h->due_date?->format('Y-m-d'),
            'attachment_url' => $h->attachmentUrl,
            'status' => $h->status,
        ]);

        return $this->paginated($data, 'Homework list retrieved.');
    }

    public function homeworkStore(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $academicYear = $this->currentAcademicYear();

        $validated = $request->validate([
            'class_section_id' => ['required', 'integer', 'exists:class_section,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
        ]);

        if (! $teacher->classSections->contains('id', (int) $validated['class_section_id'])) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        $validated['academic_year_id'] = $academicYear?->id;
        $validated['status'] = 'active';

        $homework = $this->homeworkService->create($validated);

        return $this->success([
            'id' => $homework->id,
            'title' => $homework->title,
        ], 'Homework created successfully.', Response::HTTP_CREATED);
    }

    public function homeworkUpdate(int $id, Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $homework = Homework::query()->findOrFail($id);

        if (! $teacher->classSections->contains('id', $homework->class_section_id)) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['sometimes', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
            'remove_attachment' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        $homework = $this->homeworkService->update($homework, $validated);

        return $this->success([
            'id' => $homework->id,
            'title' => $homework->title,
        ], 'Homework updated successfully.');
    }

    public function homeworkShow(int $id): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $homework = Homework::query()
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section'])
            ->findOrFail($id);

        if (! $teacher->classSections->contains('id', $homework->class_section_id)) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        return $this->success([
            'id' => $homework->id,
            'title' => $homework->title,
            'description' => $homework->description,
            'subject' => $homework->subject ? ['id' => $homework->subject->id, 'name' => $homework->subject->name] : null,
            'class_section' => $homework->classSection ? [
                'id' => $homework->classSection->id,
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
    // EXAMS
    // ────────────────────────────────────────────────────────────────────────────

    public function examsIndex(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $classSectionIds = $teacher->classSections->pluck('id');

        $query = Exam::query()
            ->whereIn('class_section_id', $classSectionIds)
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section']);

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->string('exam_type'));
        }

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
            'class_section' => $e->classSection ? [
                'id' => $e->classSection->id,
                'class' => $e->classSection->schoolClass?->name ?? '',
                'section' => $e->classSection->section?->name ?? '',
            ] : null,
            'maximum_marks' => $e->maximum_marks,
            'pass_marks' => $e->pass_marks,
            'status' => $e->status,
            'is_published' => $e->is_published,
        ]);

        return $this->paginated($data, 'Exams retrieved.');
    }

    public function examsShow(int $id): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $exam = Exam::query()
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section'])
            ->findOrFail($id);

        if (! $teacher->classSections->contains('id', $exam->class_section_id)) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        $students = StudentSession::query()
            ->where('class_section_id', $exam->class_section_id)
            ->with('student.user')
            ->where('status', 'active')
            ->get()
            ->map(function ($session) use ($exam) {
                $result = $exam->results()->where('student_id', $session->student_id)->first();

                return [
                    'student_id' => $session->student->id,
                    'uuid' => $session->student->uuid,
                    'admission_no' => $session->student->admission_no,
                    'full_name' => $session->student->full_name,
                    'roll_no' => $session->roll_no,
                    'result' => $result ? [
                        'id' => $result->id,
                        'marks_obtained' => $result->marks_obtained,
                        'grade' => $result->grade,
                        'status' => $result->status,
                        'remarks' => $result->remarks,
                    ] : null,
                ];
            });

        return $this->success([
            'exam' => [
                'id' => $exam->id,
                'exam_name' => $exam->exam_name,
                'exam_type' => $exam->exam_type,
                'exam_date' => $exam->exam_date?->format('Y-m-d'),
                'subject' => $exam->subject ? ['name' => $exam->subject->name] : null,
                'class_section' => [
                    'id' => $exam->classSection->id,
                    'class' => $exam->classSection->schoolClass?->name ?? '',
                    'section' => $exam->classSection->section?->name ?? '',
                ],
                'maximum_marks' => $exam->maximum_marks,
                'pass_marks' => $exam->pass_marks,
                'status' => $exam->status,
                'is_published' => $exam->is_published,
            ],
            'students' => $students,
            'total_students' => $students->count(),
            'results_submitted' => $students->filter(fn ($s) => $s['result'] !== null)->count(),
        ], 'Exam details retrieved.');
    }

    public function examsStoreMarks(int $examId, Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $exam = Exam::query()->findOrFail($examId);

        if (! $teacher->classSections->contains('id', $exam->class_section_id)) {
            return $this->forbidden('You are not assigned to this class section.');
        }

        $validated = $request->validate([
            'results' => ['required', 'array', 'min:1'],
            'results.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'results.*.marks_obtained' => ['required', 'integer', 'min:0', 'max:' . $exam->maximum_marks],
            'results.*.grade' => ['nullable', 'string', 'max:50'],
            'results.*.remarks' => ['nullable', 'string', 'max:1000'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $saved = $this->examService->bulkSave($exam, $validated['results']);

        if (! empty($validated['publish'])) {
            $this->examService->publish($exam);
        }

        return $this->success([
            'exam_id' => $examId,
            'results_saved' => count($saved),
            'is_published' => $exam->fresh()->is_published,
        ], 'Exam marks saved successfully.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // LEAVE
    // ────────────────────────────────────────────────────────────────────────────

    public function leaveIndex(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();

        $query = LeaveRequest::query()
            ->where('user_id', $teacher->user_id)
            ->with(['leaveType:id,name', 'student:id,first_name,last_name,admission_no']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        $data = $paginator->through(fn ($lr) => [
            'id' => $lr->id,
            'leave_type' => $lr->leaveType?->name,
            'student' => $lr->student ? [
                'id' => $lr->student->id,
                'name' => $lr->student->full_name,
                'admission_no' => $lr->student->admission_no,
            ] : null,
            'from_date' => $lr->from_date?->format('Y-m-d'),
            'to_date' => $lr->to_date?->format('Y-m-d'),
            'days' => $lr->days,
            'reason' => $lr->reason,
            'status' => $lr->status,
            'status_label' => $lr->statusLabel,
            'remarks' => $lr->remarks,
            'created_at' => $lr->created_at?->toISOString(),
        ]);

        return $this->paginated($data, 'Leave requests retrieved.');
    }

    public function leaveStore(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $validated['user_id'] = $teacher->user_id;

        $leaveRequest = $this->leaveService->create($validated);

        return $this->success([
            'id' => $leaveRequest->id,
            'status' => $leaveRequest->status,
        ], 'Leave request submitted successfully.', Response::HTTP_CREATED);
    }

    // ────────────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS
    // ────────────────────────────────────────────────────────────────────────────

    public function notificationsIndex(): JsonResponse
    {
        $userId = request()->user()->id;
        $bellData = $this->notificationService->bellData($userId);

        return $this->success(
            $bellData,
            'Notifications retrieved.'
        );
    }

    public function notificationsRead(int $id): JsonResponse
    {
        $userId = request()->user()->id;

        $notification = \App\Modules\Notifications\Models\Notification::query()->findOrFail($id);
        $this->notificationService->markRead($notification, $userId);

        return $this->success(message: 'Notification marked as read.');
    }

    public function notificationsReadAll(): JsonResponse
    {
        $userId = request()->user()->id;
        $this->notificationService->markAllRead($userId);

        return $this->success(message: 'All notifications marked as read.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // STUDENTS DIRECTORY
    // ────────────────────────────────────────────────────────────────────────────

    public function students(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $classSectionIds = $teacher->classSections->pluck('id');

        $query = StudentSession::query()
            ->whereIn('class_section_id', $classSectionIds)
            ->with(['student.user', 'classSection.schoolClass', 'classSection.section'])
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('student', function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->integer('class_section_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('student', fn ($q) => $q->where('status', $request->string('status')));
        }

        $paginator = $query->orderBy('roll_no')
            ->paginate($request->integer('per_page', 50));

        $data = $paginator->through(fn ($session) => [
            'id' => $session->student->id,
            'uuid' => $session->student->uuid,
            'admission_no' => $session->student->admission_no,
            'full_name' => $session->student->full_name,
            'roll_no' => $session->roll_no,
            'photo_url' => $session->student->photo_path ? asset('storage/' . $session->student->photo_path) : null,
            'class_name' => $session->classSection?->schoolClass?->name ?? '',
            'section' => $session->classSection?->section?->name ?? '',
            'status' => $session->student->status,
        ]);

        return $this->paginated($data, 'Students retrieved.');
    }

    public function studentShow(int $id): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $session = StudentSession::query()
            ->where('student_id', $id)
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->with(['student.parents', 'classSection.schoolClass', 'classSection.section'])
            ->where('status', 'active')
            ->firstOrFail();

        $student = $session->student;

        // Parent info from primary guardians
        $guardians = $student->parents()->wherePivot('is_primary', true)->get();
        $father = $guardians->first(fn ($g) => $g->pivot->relationship === 'father');
        $mother = $guardians->first(fn ($g) => $g->pivot->relationship === 'mother');
        $primaryAddress = $father?->address ?? $mother?->address ?? '';

        // Attendance summary
        $totalDays = Attendance::query()
            ->where('student_id', $id)
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->count();
        $presentDays = Attendance::query()
            ->where('student_id', $id)
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->where('status', 'present')
            ->count();
        $absentDays = Attendance::query()
            ->where('student_id', $id)
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->where('status', 'absent')
            ->count();
        $lateDays = Attendance::query()
            ->where('student_id', $id)
            ->whereIn('class_section_id', $teacher->classSections->pluck('id'))
            ->where('status', 'late')
            ->count();

        return $this->success([
            'id' => $student->id,
            'uuid' => $student->uuid,
            'admission_no' => $student->admission_no,
            'full_name' => $student->full_name,
            'roll_no' => $session->roll_no,
            'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
            'class_name' => $session->classSection?->schoolClass?->name ?? '',
            'section' => $session->classSection?->section?->name ?? '',
            'gender' => $student->gender ?? '',
            'date_of_birth' => $student->date_of_birth?->format('Y-m-d') ?? '',
            'blood_group' => $student->blood_group ?? null,
            'status' => $student->status,
            'parent_info' => [
                'father_name' => $father ? $father->full_name : '',
                'mother_name' => $mother ? $mother->full_name : '',
                'father_phone' => $father?->phone ?? '',
                'mother_phone' => $mother?->phone ?? '',
                'father_email' => $father?->email ?? null,
                'mother_email' => $mother?->email ?? null,
                'address' => $primaryAddress,
            ],
            'attendance' => [
                'total_days' => $totalDays,
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
            ],
            'transport' => null,
            'fee_status' => null,
            'recent_homework' => [],
        ], 'Student details retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // LEAVE TYPES
    // ────────────────────────────────────────────────────────────────────────────

    public function leaveTypes(): JsonResponse
    {
        $schoolId = app(SchoolContext::class)->id();
        $types = LeaveType::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->get(['id', 'name', 'description']);

        return $this->success([
            'leave_types' => $types,
        ], 'Leave types retrieved.');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // CLASSES (for dropdowns)
    // ────────────────────────────────────────────────────────────────────────────

    public function classes(): JsonResponse
    {
        $teacher = $this->resolveTeacher();
        $classes = $teacher->classSections()->with(['schoolClass', 'section'])->get()->map(fn ($cs) => [
            'id' => $cs->id,
            'class' => $cs->schoolClass?->name ?? '',
            'section' => $cs->section?->name ?? '',
            'is_class_teacher' => (bool) ($cs->pivot->is_class_teacher ?? false),
        ]);

        return $this->success([
            'classes' => $classes,
            'subjects' => $teacher->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code]),
        ], 'Classes retrieved.');
    }
}
