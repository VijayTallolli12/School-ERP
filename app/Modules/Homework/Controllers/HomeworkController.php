<?php

namespace App\Modules\Homework\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Homework\Models\Homework;
use App\Modules\Homework\Repositories\HomeworkRepositoryInterface;
use App\Modules\Homework\Requests\StoreHomeworkRequest;
use App\Modules\Homework\Requests\UpdateHomeworkRequest;
use App\Modules\Homework\Services\HomeworkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class HomeworkController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly HomeworkRepositoryInterface $homework,
        private readonly HomeworkService $service,
    ) {}

    public function index(): View
    {
        $classSectionsQuery = ClassSection::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'active');

        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            if ($teacher) {
                $assignedIds = $teacher->classSections->pluck('id');
                $classSectionsQuery->whereIn('id', $assignedIds);
            }
        }

        return view('modules.homework.index', [
            'academicYears' => AcademicYear::query()->where('status', 'active')->orderByDesc('starts_on')->get(),
            'classSections' => $classSectionsQuery->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(),
            'statuses' => Homework::statuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->homework->query();

        if ($classSectionId = request('class_section_id')) {
            $query->where('class_section_id', $classSectionId);
        }

        if ($subjectId = request('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            if ($teacher) {
                $assignedIds = $teacher->classSections->pluck('id');
                $query->whereIn('class_section_id', $assignedIds);
            }
        }

        return DataTables::of($query)
            ->addColumn('assigned_date', fn (Homework $hw) => $hw->assigned_date?->format('M d, Y'))
            ->addColumn('due_date', fn (Homework $hw) => $hw->due_date?->format('M d, Y'))
            ->addColumn('class_section', fn (Homework $hw) => $hw->classSection?->schoolClass->name.' - '.$hw->classSection?->section->name)
            ->addColumn('subject', fn (Homework $hw) => $hw->subject?->name ?? '-')
            ->addColumn('status_label', fn (Homework $hw) => '<span class="badge bg-'.($hw->status === 'active' ? 'success' : 'secondary').'">'.e($hw->status_label).'</span>')
            ->addColumn('actions', fn (Homework $hw) => view('modules.homework._actions', compact('hw'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function store(StoreHomeworkRequest $request): JsonResponse
    {
        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            $assignedIds = $teacher ? $teacher->classSections->pluck('id')->toArray() : [];
            if (! in_array($request->input('class_section_id'), $assignedIds)) {
                return response()->json(['success' => false, 'message' => 'You can only create homework for your assigned class sections.'], 403);
            }
        }
        $homework = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Homework created successfully.',
            'data' => $homework,
        ]);
    }

    public function show(Homework $homework): JsonResponse
    {
        $this->authorize('view', $homework);
        $homework->load(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $homework->id,
                'academic_year_id' => $homework->academic_year_id,
                'class_section_id' => $homework->class_section_id,
                'subject_id' => $homework->subject_id,
                'title' => $homework->title,
                'description' => $homework->description,
                'assigned_date' => $homework->assigned_date?->toDateString(),
                'due_date' => $homework->due_date?->toDateString(),
                'status' => $homework->status,
                'attachment' => $homework->attachment,
                'attachment_url' => $homework->attachment_url,
                'class_section' => $homework->classSection?->schoolClass->name.' - '.$homework->classSection?->section->name,
                'subject' => $homework->subject?->name,
                'academic_year' => $homework->academicYear?->name,
            ],
        ]);
    }

    public function update(UpdateHomeworkRequest $request, Homework $homework): JsonResponse
    {
        $this->authorize('update', $homework);
        $homework = $this->service->update($homework, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Homework updated successfully.',
            'data' => $homework,
        ]);
    }

    public function destroy(Homework $homework): JsonResponse
    {
        $this->authorize('delete', $homework);
        $this->service->delete($homework);

        return response()->json([
            'success' => true,
            'message' => 'Homework deleted successfully.',
        ]);
    }

    public function getSubjectsByClass(Request $request): JsonResponse
    {
        $classSectionId = $request->input('class_section_id');
        $classSection = ClassSection::query()->with('schoolClass')->find($classSectionId);

        if (! $classSection) {
            return response()->json(['success' => false, 'data' => []], 404);
        }

        $academicYearId = $request->input('academic_year_id');

        $subjects = Subject::query()
            ->where('status', 'active')
            ->whereHas('classSubjects', function ($query) use ($classSection, $academicYearId) {
                $query->where('class_id', $classSection->class_id);
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }
}
