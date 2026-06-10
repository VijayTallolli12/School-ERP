<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ExamResource;
use App\Http\Resources\Api\V1\ExamResultResource;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Exams\Repositories\ExamRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamApiController extends ApiBaseController
{
    public function __construct(
        private readonly ExamRepositoryInterface $examRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'sometimes|nullable|integer|exists:academic_years,id',
            'class_section_id' => 'sometimes|nullable|integer|exists:class_section,id',
            'subject_id' => 'sometimes|nullable|integer|exists:subjects,id',
            'exam_type' => 'sometimes|nullable|string',
            'is_published' => 'sometimes|nullable|boolean',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Exam::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject:id,name,code']);

        if ($academicYearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($classSectionId = $request->integer('class_section_id')) {
            $query->where('class_section_id', $classSectionId);
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($examType = $request->input('exam_type')) {
            $query->where('exam_type', $examType);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $paginator = $query->orderByDesc('exam_date')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Exam $e) => new ExamResource($e)),
            message: 'Exams retrieved.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $exam = Exam::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject:id,name,code'])
            ->find($id);

        if (! $exam) {
            return $this->notFound('Exam not found.');
        }

        $user = request()->user();

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Principal') && ! $user->hasRole('Teacher')) {
            $student = \App\Modules\Students\Models\Student::query()->where('user_id', $user->id)->first();

            if (! $student || $exam->class_section_id !== $student->currentSession->first()?->class_section_id) {
                return $this->forbidden('You are not authorized to view this exam.');
            }
        }

        return $this->success(new ExamResource($exam), 'Exam retrieved.');
    }

    public function results(int $examId, Request $request): JsonResponse
    {
        $exam = Exam::query()->find($examId);

        if (! $exam) {
            return $this->notFound('Exam not found.');
        }

        $user = request()->user();

        if (! $user->isSuperAdmin() && ! $user->hasRole('School Admin') && ! $user->hasRole('Principal') && ! $user->hasRole('Teacher')) {
            return $this->forbidden('You are not authorized to view exam results.');
        }

        $results = ExamResult::query()
            ->where('exam_id', $examId)
            ->with(['student:id,first_name,last_name,admission_no,uuid,roll_no'])
            ->orderBy('marks_obtained', 'desc')
            ->get();

        return $this->success([
            'exam' => new ExamResource($exam),
            'results' => ExamResultResource::collection($results),
            'summary' => [
                'total_students' => $results->count(),
                'max_marks' => $exam->maximum_marks,
                'pass_marks' => $exam->pass_marks,
                'highest' => $results->max('marks_obtained'),
                'lowest' => $results->min('marks_obtained'),
                'average' => $results->avg('marks_obtained') ? round($results->avg('marks_obtained'), 2) : null,
            ],
        ], 'Exam results retrieved.');
    }

    public function resultDetail(int $examId, int $resultId): JsonResponse
    {
        $result = ExamResult::query()
            ->where('exam_id', $examId)
            ->with(['student:id,first_name,last_name,admission_no,uuid', 'exam.subject', 'exam.classSection.schoolClass', 'exam.classSection.section'])
            ->find($resultId);

        if (! $result) {
            return $this->notFound('Result not found.');
        }

        return $this->success(new ExamResultResource($result), 'Exam result retrieved.');
    }

    public function reportCard(int $examId): JsonResponse
    {
        $exam = Exam::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject:id,name,code'])
            ->find($examId);

        if (! $exam) {
            return $this->notFound('Exam not found.');
        }

        $results = ExamResult::query()
            ->where('exam_id', $examId)
            ->with(['student:id,first_name,last_name,admission_no,uuid,roll_no'])
            ->orderBy('marks_obtained', 'desc')
            ->get();

        $gradeDistribution = [];
        foreach ($results as $result) {
            $grade = $result->grade ?? 'N/A';
            $gradeDistribution[$grade] = ($gradeDistribution[$grade] ?? 0) + 1;
        }

        // Calculate pass/fail stats
        $passCount = $results->filter(fn (ExamResult $r) => $r->marks_obtained >= $exam->pass_marks)->count();

        return $this->success([
            'exam' => new ExamResource($exam),
            'results' => ExamResultResource::collection($results),
            'summary' => [
                'total_students' => $results->count(),
                'passed' => $passCount,
                'failed' => $results->count() - $passCount,
                'pass_percentage' => $results->count() > 0
                    ? round(($passCount / $results->count()) * 100, 2)
                    : 0,
                'max_marks' => $exam->maximum_marks,
                'pass_marks' => $exam->pass_marks,
                'highest' => $results->max('marks_obtained'),
                'lowest' => $results->min('marks_obtained'),
                'average' => $results->avg('marks_obtained') ? round($results->avg('marks_obtained'), 2) : null,
            ],
            'grade_distribution' => $gradeDistribution,
        ], 'Report card retrieved.');
    }
}