<?php

namespace App\Modules\Exams\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exams\Models\ExamMark;
use App\Modules\Exams\Models\ExamSchedule;
use App\Modules\Exams\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamMarkController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ExamService $service,
    ) {}

    public function index(ExamSchedule $schedule): View
    {
        $schedule->load(['exam', 'subject']);

        return view('modules.exams.marks.index', compact('schedule'));
    }

    public function data(ExamSchedule $schedule): JsonResponse
    {
        $query = ExamMark::query()
            ->with(['student'])
            ->where('exam_schedule_id', $schedule->id);

        return DataTables::of($query)
            ->addColumn('student_name', fn (ExamMark $mark) => e($mark->student?->full_name ?? 'Unknown'))
            ->addColumn('status_label', fn (ExamMark $mark) => '<span class="badge bg-'.($mark->status === 'pass' ? 'success' : ($mark->status === 'fail' ? 'danger' : ($mark->status === 'absent' ? 'warning' : 'secondary'))).'">'.e(ucfirst($mark->status)).'</span>')
            ->addColumn('actions', fn (ExamMark $mark) => view('modules.exams.marks._actions', compact('mark'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function bulkSave(Request $request, ExamSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule->exam);

        $marks = $request->input('marks', []);

        if (empty($marks)) {
            return response()->json(['success' => false, 'message' => 'No marks data provided.'], 422);
        }

        foreach ($marks as $entry) {
            $studentId = (int) ($entry['student_id'] ?? 0);
            $marksObtained = isset($entry['marks_obtained']) ? (float) $entry['marks_obtained'] : null;
            $remarks = $entry['remarks'] ?? null;
            $absent = ! empty($entry['absent']);

            $this->service->saveMarkWithGrade($schedule, $studentId, $marksObtained, $remarks, $absent);
        }

        return response()->json([
            'success' => true,
            'message' => 'Marks saved successfully.',
        ]);
    }

    public function show(ExamMark $mark): JsonResponse
    {
        $mark->load(['student', 'examSchedule.subject']);

        return response()->json([
            'success' => true,
            'data' => $mark,
        ]);
    }

    public function update(Request $request, ExamMark $mark): JsonResponse
    {
        $this->authorize('update', $mark->examSchedule->exam);

        $validated = $request->validate([
            'marks_obtained' => 'nullable|numeric|min:0',
            'grade' => 'nullable|string|max:10',
            'grade_point' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:pass,fail,pending,absent',
            'remarks' => 'nullable|string',
        ]);

        $mark->fill([...$validated, 'updated_by' => auth()->id()])->save();

        return response()->json([
            'success' => true,
            'message' => 'Mark updated successfully.',
            'data' => $mark->fresh(),
        ]);
    }
}
