<?php

namespace App\Modules\Lifecycle\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Lifecycle\Models\StudentTransfer;
use App\Modules\Lifecycle\Requests\IssueTcRequest;
use App\Modules\Lifecycle\Requests\PromoteStudentsRequest;
use App\Modules\Lifecycle\Requests\TransferStudentRequest;
use App\Modules\Lifecycle\Services\StudentLifecycleService;
use App\Modules\Students\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StudentLifecycleController extends Controller
{
    public function __construct(private readonly StudentLifecycleService $service) {}

    public function index()
    {
        return view('modules.lifecycle.index', [
            'types' => StudentTransfer::types(),
            'students' => $this->promotableStudents(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => $this->classSections(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = StudentTransfer::query()
            ->with(['student', 'fromClassSection.schoolClass', 'fromClassSection.section', 'toClassSection.schoolClass', 'toClassSection.section']);

        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->get('transfer_type'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->get('student_id'));
        }

        return DataTables::of($query)
            ->addColumn('student_name', fn (StudentTransfer $t) => e($t->student?->full_name ?? '-'))
            ->addColumn('admission_no', fn (StudentTransfer $t) => e($t->student?->admission_no ?? '-'))
            ->addColumn('from_class', function (StudentTransfer $t): string {
                if (! $t->fromClassSection) {
                    return '-';
                }

                return e($t->fromClassSection->schoolClass->name.' - '.$t->fromClassSection->section->name);
            })
            ->addColumn('to_class', function (StudentTransfer $t): string {
                if (! $t->toClassSection) {
                    return '-';
                }

                return e($t->toClassSection->schoolClass->name.' - '.$t->toClassSection->section->name);
            })
            ->editColumn('transfer_type', fn (StudentTransfer $t) => '<span class="badge bg-info">'.e($t->type_label).'</span>')
            ->editColumn('transferred_on', fn (StudentTransfer $t) => e($t->transferred_on?->toDateString() ?? '-'))
            ->addColumn('tc_no', fn (StudentTransfer $t) => e($t->tc_no ?? '-'))
            ->addColumn('actions', fn (StudentTransfer $t) => view('modules.lifecycle._actions', compact('t'))->render())
            ->rawColumns(['transfer_type', 'actions'])
            ->toJson();
    }

    public function promoteIndex(Request $request)
    {
        return view('modules.lifecycle.promote', [
            'students' => $this->promotableStudents(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => $this->classSections(),
        ]);
    }

    private function promotableStudents()
    {
        return Student::query()
            ->where('status', 'active')
            ->whereHas('sessions', fn ($q) => $q->where('status', 'active'))
            ->with(['sessions.classSection.schoolClass', 'sessions.classSection.section'])
            ->orderBy('first_name')
            ->get()
            ->map(fn (Student $student): array => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'class' => $this->currentClassLabel($student),
                'roll_no' => $student->sessions->firstWhere('status', 'active')?->roll_no,
            ]);
    }

    public function promote(PromoteStudentsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->service->bulkPromote(
            $data['student_ids'],
            (int) $data['to_class_section_id'],
            (int) $data['to_academic_year_id'],
            $data['roll_numbers'] ?? []
        );

        $message = $result['promoted'].' student(s) promoted.';

        if ($result['skipped'] !== []) {
            $message .= ' Skipped: '.implode(', ', $result['skipped']);
        }

        return response()->json([
            'success' => $result['promoted'] > 0,
            'message' => $message,
            'data' => $result,
        ]);
    }

    public function transfer(TransferStudentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $student = Student::query()->findOrFail($data['student_id']);

        try {
            $transfer = $this->service->transfer($student, $data);

            return response()->json([
                'success' => true,
                'message' => 'Student transferred successfully.',
                'data' => ['transfer_id' => $transfer->id],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function issueTc(IssueTcRequest $request): JsonResponse
    {
        $data = $request->validated();
        $student = Student::query()->findOrFail($data['student_id']);

        try {
            $transfer = $this->service->issueTc($student, $data);

            return response()->json([
                'success' => true,
                'message' => 'Transfer certificate issued successfully. TC No: '.$transfer->tc_no,
                'data' => ['transfer_id' => $transfer->id, 'tc_no' => $transfer->tc_no],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function markAlumni(Request $request, Student $student): JsonResponse
    {
        if (! auth()->user()->can('student_lifecycle.alumni')) {
            abort(403);
        }

        try {
            $this->service->markAlumni($student);

            return response()->json([
                'success' => true,
                'message' => $student->full_name.' marked as alumni.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function printTc(StudentTransfer $transfer)
    {
        $transfer->load([
            'student.sessions.classSection.schoolClass',
            'student.sessions.classSection.section',
            'student.guardians',
            'fromClassSection.schoolClass',
            'fromClassSection.section',
        ]);

        return view('modules.lifecycle.tc-print', compact('transfer'));
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        $students = Student::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('admission_no', 'like', "%{$q}%");
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $students->map(fn (Student $s) => [
                'id' => $s->id,
                'text' => sprintf('%s (%s)', $s->full_name, $s->admission_no),
            ]),
        ]);
    }

    private function currentClassLabel(Student $student): string
    {
        $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();

        if (! $session?->classSection) {
            return '-';
        }

        return $session->classSection->schoolClass->name.' - '.$session->classSection->section->name;
    }

    private function classSections()
    {
        return ClassSection::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name);
    }
}
