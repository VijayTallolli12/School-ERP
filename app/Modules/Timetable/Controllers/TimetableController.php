<?php

namespace App\Modules\Timetable\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Timetable\Models\TimetableSlot;
use App\Modules\Timetable\Repositories\TimetableRepositoryInterface;
use App\Modules\Timetable\Requests\StoreTimetableSlotRequest;
use App\Modules\Timetable\Requests\UpdateTimetableSlotRequest;
use App\Modules\Timetable\Services\TimetableService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimetableController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TimetableRepositoryInterface $timetable,
        private readonly TimetableService $service,
    ) {}

    public function index(): View
    {
        return view('modules.timetable.index', [
            'academicYears' => AcademicYear::query()->where('status', 'active')->orderByDesc('starts_on')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get(),
            'days' => TimetableSlot::days(),
            'statuses' => TimetableSlot::statuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->timetable->filterQuery($this->timetable->query(), request()->only([
            'academic_year_id',
            'class_section_id',
            'teacher_id',
            'day_of_week',
            'status',
        ]));

        return DataTables::eloquent($query)
            ->addColumn('academic_year', fn (TimetableSlot $slot) => $slot->academicYear?->name ?? '-')
            ->addColumn('class_section', fn (TimetableSlot $slot) => $slot->classSection?->schoolClass->name.' - '.$slot->classSection?->section->name)
            ->addColumn('subject', fn (TimetableSlot $slot) => $slot->subject?->name ?? '-')
            ->addColumn('teacher', fn (TimetableSlot $slot) => $slot->teacher?->full_name ?? '-')
            ->addColumn('day', fn (TimetableSlot $slot) => e($slot->day_name))
            ->addColumn('time_range', fn (TimetableSlot $slot) => e($slot->time_range))
            ->addColumn('status_label', fn (TimetableSlot $slot) => '<span class="badge bg-'.($slot->status === 'active' ? 'success' : 'secondary').'">'.e(ucfirst($slot->status)).'</span>')
            ->addColumn('actions', fn (TimetableSlot $slot) => view('modules.timetable._actions', compact('slot'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function store(StoreTimetableSlotRequest $request): JsonResponse
    {
        $slot = $this->service->createSlot($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Timetable slot created successfully.',
            'data' => $slot,
        ]);
    }

    public function show(TimetableSlot $timetableSlot): JsonResponse
    {
        $this->authorize('view', $timetableSlot);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $timetableSlot->id,
                'academic_year_id' => $timetableSlot->academic_year_id,
                'class_section_id' => $timetableSlot->class_section_id,
                'subject_id' => $timetableSlot->subject_id,
                'teacher_id' => $timetableSlot->teacher_id,
                'day_of_week' => $timetableSlot->day_of_week,
                'period_number' => $timetableSlot->period_number,
                'period_label' => $timetableSlot->period_label,
                'start_time' => $timetableSlot->start_time,
                'end_time' => $timetableSlot->end_time,
                'room' => $timetableSlot->room,
                'status' => $timetableSlot->status,
            ],
        ]);
    }

    public function update(UpdateTimetableSlotRequest $request, TimetableSlot $timetableSlot): JsonResponse
    {
        $this->authorize('update', $timetableSlot);

        $slot = $this->service->updateSlot($timetableSlot, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Timetable slot updated successfully.',
            'data' => $slot,
        ]);
    }

    public function destroy(TimetableSlot $timetableSlot): JsonResponse
    {
        $this->authorize('delete', $timetableSlot);
        $this->service->deleteSlot($timetableSlot);

        return response()->json([
            'success' => true,
            'message' => 'Timetable slot deleted successfully.',
        ]);
    }

    public function classSchedule(): JsonResponse
    {
        $classSectionId = request('class_section_id');
        $academicYearId = request('academic_year_id');

        if (! $classSectionId || ! $academicYearId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->timetable->getForClassSchedule((int) $classSectionId, (int) $academicYearId)
                ->get()
                ->map(fn (TimetableSlot $slot) => [
                    'day_of_week' => $slot->day_of_week,
                    'day_name' => $slot->day_name,
                    'period_number' => $slot->period_number,
                    'period_label' => $slot->period_label,
                    'time_range' => $slot->time_range,
                    'subject' => $slot->subject?->name,
                    'teacher' => $slot->teacher?->full_name,
                    'room' => $slot->room,
                ]),
        ]);
    }

    public function teacherSchedule(): JsonResponse
    {
        $teacherId = request('teacher_id');
        $academicYearId = request('academic_year_id');

        if (! $teacherId || ! $academicYearId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->timetable->getForTeacherSchedule((int) $teacherId, (int) $academicYearId)
                ->get()
                ->map(fn (TimetableSlot $slot) => [
                    'day_of_week' => $slot->day_of_week,
                    'day_name' => $slot->day_name,
                    'period_number' => $slot->period_number,
                    'period_label' => $slot->period_label,
                    'time_range' => $slot->time_range,
                    'subject' => $slot->subject?->name,
                    'class_section' => $slot->classSection?->schoolClass->name.' - '.$slot->classSection?->section->name,
                    'room' => $slot->room,
                ]),
        ]);
    }

    public function printClassSchedule(): View
    {
        $classSection = ClassSection::query()
            ->with(['schoolClass', 'section'])
            ->findOrFail(request('class_section_id'));

        $academicYear = AcademicYear::query()->findOrFail(request('academic_year_id'));

        $this->authorize('print', TimetableSlot::class);

        $schedule = $this->timetable->getForClassSchedule($classSection->id, $academicYear->id)
            ->get()
            ->groupBy('day_name');

        return view('modules.timetable.print.class_schedule', compact('classSection', 'academicYear', 'schedule'));
    }

    public function printTeacherSchedule(): View
    {
        $teacher = Teacher::query()->findOrFail(request('teacher_id'));
        $academicYear = AcademicYear::query()->findOrFail(request('academic_year_id'));

        $this->authorize('print', TimetableSlot::class);

        $schedule = $this->timetable->getForTeacherSchedule($teacher->id, $academicYear->id)
            ->get()
            ->groupBy('day_name');

        return view('modules.timetable.print.teacher_schedule', compact('teacher', 'academicYear', 'schedule'));
    }
}
