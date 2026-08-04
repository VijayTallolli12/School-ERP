<?php

namespace App\Modules\Admissions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Admissions\Models\Admission;
use App\Modules\Admissions\Models\AdmissionDocument;
use App\Modules\Admissions\Requests\StoreAdmissionDocumentRequest;
use App\Modules\Admissions\Requests\StoreAdmissionRequest;
use App\Modules\Admissions\Requests\UpdateAdmissionRequest;
use App\Modules\Admissions\Services\AdmissionService;
use App\Modules\Students\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdmissionController extends Controller
{
    public function __construct(private readonly AdmissionService $service) {}

    public function index()
    {
        return view('modules.admissions.index', [
            'stats' => $this->service->stats(),
            'statuses' => Admission::statuses(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = Admission::query()
            ->with(['classSection.schoolClass', 'classSection.section', 'academicYear', 'student'])
            ->withCount('documents');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->get('class_section_id'));
        }

        return DataTables::of($query)
            ->addColumn('full_name', fn (Admission $a) => e($a->full_name))
            ->addColumn('class_section', function (Admission $a): string {
                if (! $a->classSection) {
                    return '-';
                }

                return e($a->classSection->schoolClass->name.' - '.$a->classSection->section->name);
            })
            ->addColumn('academic_year', fn (Admission $a) => e($a->academicYear?->name ?? '-'))
            ->editColumn('status', function (Admission $a): string {
                $colors = [
                    'enquiry' => 'secondary',
                    'application' => 'info',
                    'verified' => 'primary',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'converted' => 'dark',
                ];

                return '<span class="badge bg-'.($colors[$a->status] ?? 'secondary').'">'.e($a->status_label).'</span>';
            })
            ->addColumn('documents', fn (Admission $a) => $a->documents_count)
            ->addColumn('actions', fn (Admission $a) => view('modules.admissions._actions', compact('a'))->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function create()
    {
        return view('modules.admissions.create', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => $this->classSections(),
            'statuses' => Admission::statuses(),
            'sources' => Admission::sources(),
        ]);
    }

    public function store(StoreAdmissionRequest $request): RedirectResponse
    {
        $admission = $this->service->create($request->validated(), $request->file('photo'));

        return redirect()
            ->route('admin.admissions.show', $admission)
            ->with('success', 'Admission application created successfully.');
    }

    public function show(Admission $admission)
    {
        $admission->load([
            'student',
            'classSection.schoolClass',
            'classSection.section',
            'academicYear',
            'documents.verifier',
            'creator',
        ]);

        return view('modules.admissions.show', [
            'admission' => $admission,
            'documentTypes' => AdmissionDocument::documentTypes(),
            'canConvert' => in_array($admission->status, ['approved', 'verified'], true),
        ]);
    }

    public function edit(Admission $admission)
    {
        $admission->load(['classSection.schoolClass', 'classSection.section', 'academicYear']);

        return view('modules.admissions.edit', [
            'admission' => $admission,
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classSections' => $this->classSections(),
            'sources' => Admission::sources(),
        ]);
    }

    public function update(UpdateAdmissionRequest $request, Admission $admission): RedirectResponse
    {
        $this->service->update($admission, $request->validated());

        return redirect()
            ->route('admin.admissions.show', $admission)
            ->with('success', 'Admission application updated successfully.');
    }

    public function verify(Request $request, Admission $admission): JsonResponse
    {
        if (! auth()->user()->can('admissions.verify')) {
            abort(403);
        }

        try {
            $this->service->verify($admission);

            return response()->json(['success' => true, 'message' => 'Application verified.']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, Admission $admission): JsonResponse
    {
        if (! auth()->user()->can('admissions.approve')) {
            abort(403);
        }

        try {
            $this->service->approve($admission);

            return response()->json([
                'success' => true,
                'message' => 'Application approved. Admission No: '.$admission->fresh()->admission_no,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, Admission $admission): JsonResponse
    {
        if (! auth()->user()->can('admissions.reject')) {
            abort(403);
        }

        try {
            $this->service->reject($admission, $request->get('reason', ''));

            return response()->json(['success' => true, 'message' => 'Application rejected.']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function convert(Request $request, Admission $admission): JsonResponse
    {
        if (! auth()->user()->can('admissions.convert')) {
            abort(403);
        }

        try {
            $student = $this->service->convertToStudent($admission);

            return response()->json([
                'success' => true,
                'message' => 'Application converted to student successfully.',
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'student_url' => route('admin.students.show', $student),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function addDocument(StoreAdmissionDocumentRequest $request, Admission $admission): RedirectResponse
    {
        $this->service->addDocument(
            $admission,
            $request->validated('document_type'),
            $request->validated('document_name'),
            $request->file('file')
        );

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function verifyDocument(Request $request, Admission $admission, AdmissionDocument $document): JsonResponse
    {
        if (! auth()->user()->can('admissions.verify')) {
            abort(403);
        }

        if ($document->admission_id !== $admission->id) {
            abort(404);
        }

        $this->service->verifyDocument($document);

        return response()->json(['success' => true, 'message' => 'Document verified.']);
    }

    public function deleteDocument(Request $request, Admission $admission, AdmissionDocument $document): JsonResponse
    {
        if (! auth()->user()->can('admissions.delete')) {
            abort(403);
        }

        if ($document->admission_id !== $admission->id) {
            abort(404);
        }

        $this->service->deleteDocument($document);

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    public function destroy(Admission $admission): JsonResponse
    {
        activity()->causedBy(auth()->user())->performedOn($admission)->event('deleted')->log('Admission application deleted');
        $admission->delete();

        return response()->json(['success' => true, 'message' => 'Admission application deleted.']);
    }

    public function print(Admission $admission)
    {
        $admission->load(['classSection.schoolClass', 'classSection.section', 'academicYear', 'documents']);

        return view('modules.admissions.print', compact('admission'));
    }

    private function classSections()
    {
        return ClassSection::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (ClassSection $classSection) => $classSection->schoolClass->sort_order.'-'.$classSection->section->name);
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
}
