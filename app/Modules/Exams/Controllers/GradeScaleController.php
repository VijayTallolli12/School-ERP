<?php

namespace App\Modules\Exams\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exams\Models\GradeScale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GradeScaleController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        return view('modules.exams.grade-scales.index');
    }

    public function data(): JsonResponse
    {
        $query = GradeScale::query()->orderBy('sort_order');

        return DataTables::of($query)
            ->addColumn('actions', fn (GradeScale $gradeScale) => view('modules.exams.grade-scales._actions', compact('gradeScale'))->render())
            ->editColumn('is_fail', fn (GradeScale $gradeScale) => $gradeScale->is_fail ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>')
            ->editColumn('status', fn (GradeScale $gradeScale) => '<span class="badge bg-'.($gradeScale->status === 'active' ? 'success' : 'secondary').'">'.e(ucfirst($gradeScale->status)).'</span>')
            ->rawColumns(['is_fail', 'status', 'actions'])
            ->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', GradeScale::class);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'grade' => 'required|string|max:10',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'grade_point' => 'nullable|numeric|min:0|max:100',
            'is_fail' => 'boolean',
            'sort_order' => 'integer|min:0',
            'status' => 'string|in:active,inactive',
        ]);

        $gradeScale = GradeScale::query()->create([
            ...$validated,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grade scale created successfully.',
            'data' => $gradeScale,
        ]);
    }

    public function show(GradeScale $gradeScale): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $gradeScale,
        ]);
    }

    public function update(Request $request, GradeScale $gradeScale): JsonResponse
    {
        $this->authorize('update', $gradeScale);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'grade' => 'required|string|max:10',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'grade_point' => 'nullable|numeric|min:0|max:100',
            'is_fail' => 'boolean',
            'sort_order' => 'integer|min:0',
            'status' => 'string|in:active,inactive',
        ]);

        $gradeScale->fill([...$validated, 'updated_by' => auth()->id()])->save();

        return response()->json([
            'success' => true,
            'message' => 'Grade scale updated successfully.',
            'data' => $gradeScale->fresh(),
        ]);
    }

    public function destroy(GradeScale $gradeScale): JsonResponse
    {
        $this->authorize('delete', $gradeScale);

        $gradeScale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grade scale deleted successfully.',
        ]);
    }
}
