<?php

namespace App\Modules\Parents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Repositories\ParentRepositoryInterface;
use App\Modules\Parents\Requests\StoreParentRequest;
use App\Modules\Parents\Requests\UpdateParentRequest;
use App\Modules\Parents\Services\ParentService;
use App\Modules\Students\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ParentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ParentRepositoryInterface $parents,
        private readonly ParentService $service,
    ) {}

    public function index(): View
    {
        return view('modules.parents.index', [
            'statuses' => Guardian::statuses(),
            'students' => Student::query()
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->parents->filterQuery($this->parents->query(), request()->only([
            'status',
            'search',
        ]))->withCount('students');

        return DataTables::eloquent($query)
            ->addColumn('full_name', fn (Guardian $parent) => e($parent->full_name))
            ->addColumn('email', fn (Guardian $parent) => e($parent->email))
            ->addColumn('phone', fn (Guardian $parent) => e($parent->phone ?? '-'))
            ->addColumn('students_count', fn (Guardian $parent) => $parent->students_count)
            ->addColumn('status_label', fn (Guardian $parent) => '<span class="badge bg-'.($parent->status === 'active' ? 'success' : 'secondary').'">'.e(ucfirst($parent->status)).'</span>')
            ->addColumn('actions', fn (Guardian $parent) => view('modules.parents._actions', compact('parent'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function store(StoreParentRequest $request): JsonResponse
    {
        $parent = $this->service->createParent($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Parent created successfully.',
            'data' => $parent,
        ]);
    }

    public function show(Guardian $parent): JsonResponse
    {
        $this->authorize('view', $parent);

        $parent->load(['students' => function ($query) {
            $query->with(['sessions' => function ($sessionQuery) {
                $sessionQuery->with('classSection.schoolClass', 'classSection.section')
                    ->where('status', 'active');
            }]);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $parent->id,
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'email' => $parent->email,
                'phone' => $parent->phone,
                'occupation' => $parent->occupation,
                'address' => $parent->address,
                'status' => $parent->status,
                'students' => $parent->students->map(function ($student) {
                    $session = $student->sessions->first();
                    return [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'admission_no' => $student->admission_no,
                        'class_section' => $session ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name : '-',
                        'relationship' => $student->pivot->relationship,
                        'is_primary' => $student->pivot->is_primary,
                    ];
                }),
            ],
        ]);
    }

    public function update(UpdateParentRequest $request, Guardian $parent): JsonResponse
    {
        $this->authorize('update', $parent);

        $parent = $this->service->updateParent($parent, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Parent updated successfully.',
            'data' => $parent,
        ]);
    }

    public function destroy(Guardian $parent): JsonResponse
    {
        $this->authorize('delete', $parent);
        $this->service->deleteParent($parent);

        return response()->json([
            'success' => true,
            'message' => 'Parent deleted successfully.',
        ]);
    }

    public function dashboard(): View
    {
        $parent = auth()->user()->guardian;

        if (!$parent) {
            abort(403, 'Parent profile not found.');
        }

        $dashboardData = $this->service->getParentDashboardData($parent);

        return view('modules.parents.dashboard', $dashboardData);
    }

    public function attendance(): View
    {
        $parent = auth()->user()->guardian;

        return view('modules.parents.attendance', [
            'students' => $parent->students,
        ]);
    }

    public function fees(): View
    {
        $parent = auth()->user()->guardian;

        return view('modules.parents.fees', [
            'students' => $parent->students,
        ]);
    }

    public function examResults(): View
    {
        $parent = auth()->user()->guardian;

        return view('modules.parents.exam_results', [
            'students' => $parent->students,
        ]);
    }

    public function timetable(): View
    {
        $parent = auth()->user()->guardian;

        return view('modules.parents.timetable', [
            'students' => $parent->students,
        ]);
    }

    public function notifications(): View
    {
        $parent = auth()->user()->guardian;

        return view('modules.parents.notifications', [
            'notifications' => $parent->notifications()->latest()->paginate(20),
        ]);
    }

    public function homework(): View
    {
        $parent = auth()->user()->guardian;

        $homework = $this->service->getHomeworkForStudents($parent->students);

        return view('modules.parents.homework', [
            'homework' => $homework,
        ]);
    }
}