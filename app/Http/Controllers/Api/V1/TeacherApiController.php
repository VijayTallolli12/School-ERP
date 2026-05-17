<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\TeacherListResource;
use App\Http\Resources\Api\V1\TeacherResource;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Timetable\Models\TimetableSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'sometimes|nullable|string|max:100',
            'status' => 'sometimes|nullable|in:active,inactive',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Teacher::query()->with(['user', 'subjects']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('first_name')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Teacher $t) => new TeacherListResource($t)),
            message: 'Teachers retrieved.'
        );
    }

    public function show(string $uuid): JsonResponse
    {
        $teacher = Teacher::query()
            ->where('uuid', $uuid)
            ->with([
                'user',
                'subjects',
                'classSections.schoolClass',
                'classSections.section',
                'classTeacherSections.schoolClass',
                'classTeacherSections.section',
                'documents',
            ])
            ->first();

        if (! $teacher) {
            return $this->notFound('Teacher not found.');
        }

        return $this->success(new TeacherResource($teacher), 'Teacher retrieved.');
    }

    public function timetable(string $uuid, Request $request): JsonResponse
    {
        $teacher = Teacher::query()->where('uuid', $uuid)->first();

        if (! $teacher) {
            return $this->notFound('Teacher not found.');
        }

        $slotsQuery = TimetableSlot::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject:id,name,code', 'classSection.schoolClass', 'classSection.section']);

        if ($academicYearId = $request->integer('academic_year_id')) {
            $slotsQuery->where('academic_year_id', $academicYearId);
        }

        $slots = $slotsQuery
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
                'class_section' => $slot->classSection ? [
                    'id' => $slot->classSection->id,
                    'class' => $slot->classSection->schoolClass->name ?? '',
                    'section' => $slot->classSection->section->name ?? '',
                ] : null,
                'room' => $slot->room,
            ]));

        return $this->success([
            'teacher' => new TeacherListResource($teacher),
            'timetable' => $slots,
        ], 'Teacher timetable retrieved.');
    }

    public function attendance(string $uuid, Request $request): JsonResponse
    {
        $teacher = Teacher::query()->where('uuid', $uuid)->first();

        if (! $teacher) {
            return $this->notFound('Teacher not found.');
        }

        $request->validate([
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:2000|max:2100',
        ]);

        $month = $request->integer('month', (int) now()->month);
        $year = $request->integer('year', (int) now()->year);

        $records = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();

        $statuses = TeacherAttendance::statuses();
        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status] = $records->where('status', $status)->count();
        }

        return $this->success([
            'teacher' => new TeacherListResource($teacher),
            'month' => $month,
            'year' => $year,
            'summary' => [
                'total_days' => $records->count(),
                'counts' => $counts,
            ],
            'records' => $records->map(fn ($r) => [
                'id' => $r->id,
                'attendance_date' => $r->attendance_date?->format('Y-m-d'),
                'status' => $r->status,
                'status_label' => $r->status_label,
                'remarks' => $r->remarks,
            ]),
        ], 'Teacher attendance retrieved.');
    }

    public function assignedClasses(string $uuid): JsonResponse
    {
        $teacher = Teacher::query()->where('uuid', $uuid)->with([
            'classSections.schoolClass',
            'classSections.section',
            'classTeacherSections.schoolClass',
            'classTeacherSections.section',
        ])->first();

        if (! $teacher) {
            return $this->notFound('Teacher not found.');
        }

        return $this->success([
            'teacher' => new TeacherListResource($teacher),
            'classes' => $teacher->classSections->map(fn ($cs) => [
                'id' => $cs->id,
                'class' => $cs->schoolClass->name ?? '',
                'section' => $cs->section->name ?? '',
                'is_class_teacher' => (bool) ($cs->pivot->is_class_teacher ?? false),
            ]),
            'class_teacher_sections' => $teacher->classTeacherSections->map(fn ($cs) => [
                'id' => $cs->id,
                'class' => $cs->schoolClass->name ?? '',
                'section' => $cs->section->name ?? '',
            ]),
        ], 'Assigned classes retrieved.');
    }

    public function assignedSubjects(string $uuid): JsonResponse
    {
        $teacher = Teacher::query()->where('uuid', $uuid)->with('subjects')->first();

        if (! $teacher) {
            return $this->notFound('Teacher not found.');
        }

        return $this->success([
            'teacher' => new TeacherListResource($teacher),
            'subjects' => $teacher->subjects->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code ?? null,
            ]),
        ], 'Assigned subjects retrieved.');
    }
}