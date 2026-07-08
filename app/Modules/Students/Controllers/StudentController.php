<?php

namespace App\Modules\Students\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Repositories\StudentRepositoryInterface;
use App\Modules\Students\Requests\StoreStudentRequest;
use App\Modules\Students\Requests\UpdateStudentRequest;
use App\Modules\Students\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentRepositoryInterface $students,
        private readonly StudentService $service,
    ) {}

    public function index()
    {
        /** @var \App\Modules\Parents\Models\Guardian[] $parents */
        $parents = Guardian::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        logger()->debug('StudentController@index — parents dropdown', [
            'count' => $parents->count(),
            'school_context_id' => app(\App\Core\Tenant\SchoolContext::class)->id(),
            'auth_user_school_id' => auth()->user()->current_school_id,
            'auth_user_id' => auth()->id(),
            'parent_ids' => $parents->pluck('id')->toArray(),
            'parent_names' => $parents->pluck('first_name')->toArray(),
            'sql' => $parents->toQuery()->toSql(),
        ]);

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

        return view('modules.students.index', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => $classSectionsQuery->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
            'parents' => $parents,
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->students->query();

        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            if ($teacher) {
                $assignedIds = $teacher->classSections->pluck('id');
                $query->whereHas('sessions', function ($q) use ($assignedIds) {
                    $q->whereIn('class_section_id', $assignedIds)->where('status', 'active');
                });
            }
        }

        return DataTables::of($query)
            ->addColumn('full_name', fn (Student $student) => e($student->full_name))
            ->addColumn('class_section', function (Student $student): string {
                $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();

                if (! $session?->classSection) {
                    return '-';
                }

                return e($session->classSection->schoolClass->name.' - '.$session->classSection->section->name);
            })
            ->addColumn('guardian', function (Student $student): string {
                $primaryGuardian = $student->guardians->firstWhere('is_primary', true);
                if ($primaryGuardian?->name) {
                    return e($primaryGuardian->name);
                }
                $primaryParent = $student->parents->first();
                return e($primaryParent?->full_name ?? '-');
            })
            ->addColumn('actions', fn (Student $student) => view('modules.students._actions', compact('student'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->service->create($request->validated(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Student admitted successfully.',
            'data' => $student,
        ]);
    }

    public function show(Student $student)
    {
        $student->load([
            'guardians',
            'parents',
            'sessions.academicYear',
            'sessions.classSection.schoolClass',
            'sessions.classSection.section',
        ]);

        $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();
        $guardian = $student->guardians->firstWhere('is_primary', true);
        $primaryParent = $student->parents->first();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'admission_no' => $student->admission_no,
                    'admission_date' => $student->admission_date?->toDateString(),
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'date_of_birth' => $student->date_of_birth?->toDateString(),
                    'gender' => $student->gender,
                    'blood_group' => $student->blood_group,
                    'religion' => $student->religion,
                    'category' => $student->category,
                    'caste' => $student->caste,
                    'nationality' => $student->nationality,
                    'mother_tongue' => $student->mother_tongue,
                    'aadhar_no' => $student->aadhar_no,
                    'current_address' => $student->current_address,
                    'permanent_address' => $student->permanent_address,
                    'status' => $student->status,
                    'academic_year_id' => $session?->academic_year_id,
                    'class_section_id' => $session?->class_section_id,
                    'roll_no' => $session?->roll_no,
                    'parent_id' => $primaryParent?->id,
                    'relationship' => $primaryParent?->pivot?->relationship ?? 'guardian',
                    'guardian_name' => $guardian?->name ?? $primaryParent?->full_name,
                    'guardian_relation' => $guardian?->relation ?? $primaryParent?->pivot?->relationship ?? 'guardian',
                    'guardian_phone' => $guardian?->phone ?? $primaryParent?->phone,
                    'guardian_email' => $guardian?->email ?? $primaryParent?->email,
                    'guardian_occupation' => $guardian?->occupation ?? $primaryParent?->occupation,
                    'guardians' => $student->guardians
                        ->sortByDesc('is_primary')
                        ->values()
                        ->map(fn ($guardian): array => [
                            'id' => $guardian->id,
                            'name' => $guardian->name,
                            'relation' => $guardian->relation,
                            'phone' => $guardian->phone,
                            'email' => $guardian->email,
                            'occupation' => $guardian->occupation,
                            'is_primary' => $guardian->is_primary,
                            'can_pickup' => $guardian->can_pickup,
                        ])
                        ->all(),
                ],
            ]);
        }

        return view('modules.students.show', compact('student', 'session', 'guardian'));
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student = $this->service->update($student, $request->validated(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data' => $student,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $students = Student::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('admission_no', 'like', "%{$q}%");
            });

        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            if ($teacher) {
                $assignedIds = $teacher->classSections->pluck('id');
                $students->whereHas('sessions', function ($q) use ($assignedIds) {
                    $q->whereIn('class_section_id', $assignedIds)->where('status', 'active');
                });
            }
        }

        $students = $students->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $students->map(fn (Student $s) => [
                'id' => $s->id,
                'text' => sprintf('%s (%s)', $s->full_name, $s->admission_no),
            ]),
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);
        $this->service->delete($student);

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.',
        ]);
    }
}
