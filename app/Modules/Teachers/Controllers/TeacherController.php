<?php

namespace App\Modules\Teachers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Teachers\Models\TeacherLeave;
use App\Modules\Teachers\Repositories\TeacherRepositoryInterface;
use App\Modules\Teachers\Requests\StoreTeacherRequest;
use App\Modules\Teachers\Requests\UpdateTeacherRequest;
use App\Modules\Teachers\Requests\MarkTeacherAttendanceRequest;
use App\Modules\Teachers\Requests\UpdateTeacherAttendanceRequest;
use App\Modules\Teachers\Requests\StoreTeacherLeaveRequest;
use App\Modules\Teachers\Requests\UpdateTeacherLeaveRequest;
use App\Modules\Teachers\Requests\TeacherAttendanceReportFilterRequest;
use App\Modules\Teachers\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function __construct(
        private readonly TeacherRepositoryInterface $teachers,
        private readonly TeacherService $service,
    ) {}

    public function index(): View
    {
        return view('modules.teachers.index', [
            'subjects' => Subject::query()->where('status', 'active')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
            'statuses' => Teacher::statuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->teachers->query())
            ->addColumn('full_name', fn (Teacher $teacher) => e($teacher->full_name))
            ->addColumn('subjects', fn (Teacher $teacher) => e($teacher->subjects->pluck('name')->join(', ')))
            ->addColumn('classes', fn (Teacher $teacher) => e($teacher->classSections->map(fn ($classSection) => $classSection->schoolClass->name.' - '.$classSection->section->name)->join(', ')))
            ->addColumn('class_teacher', fn (Teacher $teacher) => e($teacher->classTeacherSections->map(fn ($classSection) => $classSection->schoolClass->name.' - '.$classSection->section->name)->join(', ')))
            ->addColumn('actions', fn (Teacher $teacher) => view('modules.teachers._actions', compact('teacher'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->service->create($request->validated(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Teacher profile created successfully.',
            'data' => $teacher,
        ]);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['subjects', 'classSections', 'classTeacherSections', 'documents']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teacher->id,
                'employee_id' => $teacher->employee_id,
                'first_name' => $teacher->first_name,
                'middle_name' => $teacher->middle_name,
                'last_name' => $teacher->last_name,
                'gender' => $teacher->gender,
                'date_of_birth' => $teacher->date_of_birth?->toDateString(),
                'qualification' => $teacher->qualification,
                'experience_years' => $teacher->experience_years,
                'joining_date' => $teacher->joining_date?->toDateString(),
                'phone' => $teacher->phone,
                'email' => $teacher->email,
                'address' => $teacher->address,
                'status' => $teacher->status,
                'subject_ids' => $teacher->subjects->pluck('id')->all(),
                'class_section_ids' => $teacher->classSections->pluck('id')->all(),
                'class_teacher_section_ids' => $teacher->classTeacherSections->pluck('id')->all(),
            ],
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $teacher = $this->service->update($teacher, $request->validated(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Teacher profile updated successfully.',
            'data' => $teacher,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $teachers = Teacher::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('employee_id', 'like', "%{$q}%");
            })
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $teachers->map(fn (Teacher $t) => [
                'id' => $t->id,
                'text' => sprintf('%s (%s)', $t->full_name, $t->employee_id),
            ]),
        ]);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $this->authorize('delete', $teacher);
        $this->service->delete($teacher);

        return response()->json([
            'success' => true,
            'message' => 'Teacher profile deleted successfully.',
        ]);
    }

    public function attendanceIndex(): View
    {
        return view('modules.teachers.attendance', [
            'teachers' => Teacher::query()->orderBy('first_name')->get(),
            'statuses' => TeacherAttendance::statuses(),
        ]);
    }

    public function attendanceData(): JsonResponse
    {
        return DataTables::of(TeacherAttendance::query()->with(['teacher', 'markedBy']))
            ->addColumn('teacher_name', fn (TeacherAttendance $attendance) => e($attendance->teacher->full_name))
            ->addColumn('attendance_date', fn (TeacherAttendance $attendance) => $attendance->attendance_date->format('d-M-Y'))
            ->addColumn('status', function (TeacherAttendance $attendance): string {
                $statusClass = match ($attendance->status) {
                    'present' => 'success',
                    'absent' => 'danger',
                    'late' => 'warning',
                    'half_day' => 'info',
                    'excused' => 'secondary',
                    default => 'dark',
                };

                return '<span class="badge bg-'.$statusClass.'">'.e($attendance->status_label).'</span>';
            })
            ->addColumn('marked_by', fn (TeacherAttendance $attendance) => e($attendance->markedBy?->name ?? '-'))
            ->addColumn('actions', fn (TeacherAttendance $attendance) => view('modules.teachers._actions_attendance', compact('attendance'))->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function attendanceStore(MarkTeacherAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->service->recordAttendance($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teacher attendance recorded successfully.',
            'data' => $attendance,
        ]);
    }

    public function attendanceShow(TeacherAttendance $attendance): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'teacher_id' => $attendance->teacher_id,
                'attendance_date' => $attendance->attendance_date->toDateString(),
                'status' => $attendance->status,
                'remarks' => $attendance->remarks,
            ],
        ]);
    }

    public function attendanceUpdate(UpdateTeacherAttendanceRequest $request, TeacherAttendance $attendance): JsonResponse
    {
        $attendance = $this->service->updateAttendance($attendance, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teacher attendance updated successfully.',
            'data' => $attendance,
        ]);
    }

    public function attendanceDestroy(TeacherAttendance $attendance): JsonResponse
    {
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher attendance deleted successfully.',
        ]);
    }

    public function leaveIndex(): View
    {
        return view('modules.teachers.leaves', [
            'teachers' => Teacher::query()->orderBy('first_name')->get(),
            'statuses' => TeacherLeave::statuses(),
        ]);
    }

    public function leaveData(): JsonResponse
    {
        return DataTables::of(TeacherLeave::query()->with(['teacher', 'approvedBy']))
            ->addColumn('teacher_name', fn (TeacherLeave $leave) => e($leave->teacher->full_name))
            ->addColumn('period', fn (TeacherLeave $leave) => e($leave->start_date->format('d-M-Y').' to '.$leave->end_date->format('d-M-Y')))
            ->addColumn('status', function (TeacherLeave $leave): string {
                $statusClass = match ($leave->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$statusClass.'">'.ucfirst($leave->status).'</span>';
            })
            ->addColumn('approved_by', fn (TeacherLeave $leave) => e($leave->approvedBy?->name ?? '-'))
            ->addColumn('actions', fn (TeacherLeave $leave) => view('modules.teachers._actions_leave', compact('leave'))->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function leaveStore(StoreTeacherLeaveRequest $request): JsonResponse
    {
        $leave = $this->service->requestLeave($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully.',
            'data' => $leave,
        ]);
    }

    public function leaveShow(TeacherLeave $leave): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leave->id,
                'teacher_id' => $leave->teacher_id,
                'leave_type' => $leave->leave_type,
                'start_date' => $leave->start_date->toDateString(),
                'end_date' => $leave->end_date->toDateString(),
                'reason' => $leave->reason,
                'status' => $leave->status,
                'remarks' => $leave->remarks,
            ],
        ]);
    }

    public function leaveUpdate(UpdateTeacherLeaveRequest $request, TeacherLeave $leave): JsonResponse
    {
        $leave = $this->service->updateLeave($leave, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully.',
            'data' => $leave,
        ]);
    }

    public function leaveDestroy(TeacherLeave $leave): JsonResponse
    {
        $this->service->deleteLeave($leave);

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully.',
        ]);
    }

    public function subjectAllocationReport(): View
    {
        return view('modules.teachers.reports.subject_allocation', [
            'teachers' => $this->service->getSubjectAllocationReport(),
        ]);
    }

    public function attendanceReport(TeacherAttendanceReportFilterRequest $request): View
    {
        return view('modules.teachers.reports.attendance', [
            'rows' => $this->service->getAttendanceReport($request->filterPayload()),
            'teachers' => Teacher::query()->orderBy('first_name')->get(),
            'filters' => $request->filterPayload(),
        ]);
    }
}
