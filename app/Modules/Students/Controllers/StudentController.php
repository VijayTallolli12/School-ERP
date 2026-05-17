<?php

namespace App\Modules\Students\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Repositories\StudentRepositoryInterface;
use App\Modules\Students\Requests\StoreStudentRequest;
use App\Modules\Students\Requests\UpdateStudentRequest;
use App\Modules\Students\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentRepositoryInterface $students,
        private readonly StudentService $service,
    ) {}

    public function index()
    {
        return view('modules.students.index', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->students->query())
            ->addColumn('full_name', fn (Student $student) => e($student->full_name))
            ->addColumn('class_section', function (Student $student): string {
                $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();

                if (! $session?->classSection) {
                    return '-';
                }

                return e($session->classSection->schoolClass->name.' - '.$session->classSection->section->name);
            })
            ->addColumn('guardian', fn (Student $student) => e($student->guardians->firstWhere('is_primary', true)?->name ?? '-'))
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

    public function show(Student $student): JsonResponse
    {
        $student->load([
            'guardians',
            'sessions.academicYear',
            'sessions.classSection.schoolClass',
            'sessions.classSection.section',
        ]);

        $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();
        $guardian = $student->guardians->firstWhere('is_primary', true);

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
                'guardian_name' => $guardian?->name,
                'guardian_relation' => $guardian?->relation,
                'guardian_phone' => $guardian?->phone,
                'guardian_email' => $guardian?->email,
                'guardian_occupation' => $guardian?->occupation,
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

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student = $this->service->update($student, $request->validated(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data' => $student,
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
