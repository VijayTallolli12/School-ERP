<?php

namespace App\Modules\Exams\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Exams\Repositories\ExamRepositoryInterface;
use App\Modules\Exams\Requests\StoreExamRequest;
use App\Modules\Exams\Requests\UpdateExamRequest;
use App\Modules\Exams\Requests\StoreExamResultRequest;
use App\Modules\Exams\Requests\UpdateExamResultRequest;
use App\Modules\Exams\Services\ExamService;
use App\Modules\Students\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class ExamController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly ExamRepositoryInterface $exams,
        private readonly ExamService $service,
    ) {}

    public function index(): View
    {
        return view('modules.exams.index', [
            'academicYears' => AcademicYear::query()->where('status', 'active')->orderByDesc('starts_on')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(),
            'exams' => Exam::query()->orderByDesc('exam_date')->get(),
            'examTypes' => Exam::types(),
            'statuses' => Exam::statuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->exams->query())
            ->addColumn('academic_year', fn (Exam $exam) => $exam->academicYear?->name ?? '-')
            ->addColumn('class_section', fn (Exam $exam) => $exam->classSection?->schoolClass->name.' - '.$exam->classSection?->section->name)
            ->addColumn('subject', fn (Exam $exam) => $exam->subject?->name ?? '-')
            ->addColumn('status_label', fn (Exam $exam) => '<span class="badge bg-'.($exam->status === 'scheduled' ? 'secondary' : ($exam->status === 'completed' ? 'success' : 'danger')).'">'.e($exam->status_label).'</span>')
            ->addColumn('published', fn (Exam $exam) => $exam->is_published ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>')
            ->addColumn('actions', fn (Exam $exam) => view('modules.exams._actions', compact('exam'))->render())
            ->rawColumns(['status_label', 'published', 'actions'])
            ->toJson();
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam created successfully.',
            'data' => $exam,
        ]);
    }

    public function show(Exam $exam): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $exam->id,
                'exam_name' => $exam->exam_name,
                'exam_type' => $exam->exam_type,
                'academic_year_id' => $exam->academic_year_id,
                'class_section_id' => $exam->class_section_id,
                'subject_id' => $exam->subject_id,
                'exam_date' => $exam->exam_date?->toDateString(),
                'maximum_marks' => $exam->maximum_marks,
                'pass_marks' => $exam->pass_marks,
                'status' => $exam->status,
                'is_published' => $exam->is_published,
            ],
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->service->update($exam, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam updated successfully.',
            'data' => $exam,
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->authorize('delete', $exam);
        $this->service->delete($exam);

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully.',
        ]);
    }

    public function publish(Exam $exam): JsonResponse
    {
        $this->authorize('publish', $exam);
        $exam = $this->service->publish($exam);

        return response()->json([
            'success' => true,
            'message' => $exam->is_published ? 'Exam published successfully.' : 'Exam unpublished successfully.',
            'data' => $exam,
        ]);
    }

    public function resultsData(): JsonResponse
    {
        $examId = request('exam_id');

        if (! $examId) {
            return DataTables::of(ExamResult::query()->whereRaw('0 = 1'))->toJson();
        }

        $exam = Exam::query()->find($examId);

        if (! $exam) {
            return DataTables::of(ExamResult::query()->whereRaw('0 = 1'))->toJson();
        }

        $query = $this->exams->resultsQuery($exam);

        return DataTables::eloquent($query)
            ->addColumn('student_name', fn (ExamResult $result) => e($result->student?->full_name ?? '-'))
            ->addColumn('exam_name', fn (ExamResult $result) => e($result->exam->exam_name))
            ->addColumn('class_section', fn (ExamResult $result) => e($result->exam->classSection?->schoolClass->name.' - '.$result->exam->classSection?->section->name))
            ->addColumn('status_label', fn (ExamResult $result) => '<span class="badge bg-'.($result->status === 'pass' ? 'success' : 'danger').'">'.e($result->status_label).'</span>')
            ->addColumn('actions', fn (ExamResult $result) => view('modules.exams._results_actions', compact('result'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function storeResult(StoreExamResultRequest $request): JsonResponse
    {
        $this->authorize('update', Exam::query()->findOrFail($request->input('exam_id')));

        $result = $this->service->saveResult($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam result saved successfully.',
            'data' => $result,
        ]);
    }

    public function showResult(ExamResult $result): JsonResponse
    {
        $this->authorize('view', $result->exam);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $result->id,
                'exam_id' => $result->exam_id,
                'student_id' => $result->student_id,
                'marks_obtained' => $result->marks_obtained,
                'grade' => $result->grade,
                'remarks' => $result->remarks,
            ],
        ]);
    }

    public function updateResult(UpdateExamResultRequest $request, ExamResult $result): JsonResponse
    {
        $this->authorize('update', $result->exam);

        $result = $this->service->updateResult($result, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam result updated successfully.',
            'data' => $result,
        ]);
    }

    public function destroyResult(ExamResult $result): JsonResponse
    {
        $this->authorize('delete', $result->exam);
        $this->service->deleteResult($result);

        return response()->json([
            'success' => true,
            'message' => 'Exam result deleted successfully.',
        ]);
    }

    public function getStudentsByClassSection(ClassSection $classSection): JsonResponse
    {
        $students = Student::query()
            ->whereHas('sessions', function ($query) use ($classSection) {
                $query->where('class_section_id', $classSection->id)
                    ->where('status', 'active');
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json([
            'success' => true,
            'data' => $students->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->full_name,
            ]),
        ]);
    }
}
