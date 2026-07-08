<?php

namespace App\Modules\Exams\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Exam $exam): View
    {
        $subjects = Subject::query()->where('status', 'active')->orderBy('name')->get();

        return view('modules.exams.schedules.index', compact('exam', 'subjects'));
    }

    public function data(Exam $exam): JsonResponse
    {
        $query = ExamSchedule::query()
            ->with(['subject'])
            ->where('exam_id', $exam->id)
            ->orderBy('exam_date')
            ->orderBy('sort_order');

        return DataTables::of($query)
            ->addColumn('subject_name', fn (ExamSchedule $schedule) => e($schedule->subject?->name ?? '-'))
            ->addColumn('actions', fn (ExamSchedule $schedule) => view('modules.exams.schedules._actions', compact('schedule'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(Request $request, Exam $exam): JsonResponse
    {
        $this->authorize('update', $exam);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'room' => 'nullable|string|max:100',
            'maximum_marks' => 'required|integer|min:1',
            'pass_marks' => 'required|integer|min:0|lte:maximum_marks',
            'sort_order' => 'integer|min:0',
        ]);

        $schedule = ExamSchedule::query()->create([
            'exam_id' => $exam->id,
            ...$validated,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule created successfully.',
            'data' => $schedule,
        ]);
    }

    public function show(ExamSchedule $schedule): JsonResponse
    {
        $schedule->load(['subject', 'exam']);

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }

    public function update(Request $request, ExamSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule->exam);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'room' => 'nullable|string|max:100',
            'maximum_marks' => 'required|integer|min:1',
            'pass_marks' => 'required|integer|min:0|lte:maximum_marks',
            'sort_order' => 'integer|min:0',
        ]);

        $schedule->fill([...$validated, 'updated_by' => auth()->id()])->save();

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule updated successfully.',
            'data' => $schedule->fresh(),
        ]);
    }

    public function destroy(ExamSchedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule->exam);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule deleted successfully.',
        ]);
    }
}
