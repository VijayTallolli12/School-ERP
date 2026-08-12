<?php

namespace App\Modules\Documents\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Documents\Repositories\DocumentRepositoryInterface;
use App\Modules\Documents\Requests\StoreDocumentRequest;
use App\Modules\Documents\Requests\UpdateDocumentRequest;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentRepositoryInterface $repository,
        private readonly DocumentService $service,
    ) {}

    public function index(): View
    {
        if (auth()->user()->hasRole('Teacher')) {
            abort(403, 'Teachers cannot browse student documents.');
        }
        $this->authorize('viewAny', StudentDocument::class);

        return view('modules.documents.index', [
            'documentTypes' => StudentDocument::documentTypes(),
            'documentCategories' => StudentDocument::documentCategories(),
            'classes' => SchoolClass::query()->whereHas('sections')->with('sections')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentDocument::class);

        $query = $this->repository->query($request->only([
            'student_id', 'document_type', 'class_id', 'is_verified',
        ]));

        return DataTables::of($query)
            ->addColumn('student', fn (StudentDocument $doc) => view('modules.documents._student', ['doc' => $doc])->render())
            ->addColumn('class', fn (StudentDocument $doc) => $doc->student->sessions->where('status', 'active')->first()?->schoolClass?->name ?? '-')
            ->addColumn('document_type', fn (StudentDocument $doc) => '<span class="badge bg-secondary">' . e($doc->document_type_label) . '</span>')
            ->addColumn('uploaded_at', fn (StudentDocument $doc) => $doc->created_at?->format('d M Y h:i A') ?? '-')
            ->addColumn('issue_date', fn (StudentDocument $doc) => $doc->issue_date?->format('d M Y') ?? '-')
            ->addColumn('expiry_date', fn (StudentDocument $doc) => $doc->expiry_date?->format('d M Y') ?? '-')
            ->addColumn('status', fn (StudentDocument $doc) => view('modules.documents._status', ['doc' => $doc])->render())
            ->addColumn('actions', fn (StudentDocument $doc) => view('modules.documents._actions', ['doc' => $doc])->render())
            ->filterColumn('student', fn ($query, $keyword) => $query->whereHas('student', fn ($q) => $q->where('first_name', 'like', "%{$keyword}%")->orWhere('last_name', 'like', "%{$keyword}%")))
            ->rawColumns(['student', 'document_type', 'status', 'actions'])
            ->make(true);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentDocument::class);

        $q = trim((string) $request->get('q', ''));
        $limit = min((int) $request->get('limit', 20), 50);

        $students = Student::query()
            ->with(['sessions' => function ($query): void {
                $query->where('status', 'active')
                    ->with(['classSection.schoolClass', 'classSection.section'])
                    ->latest();
            }])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('first_name', 'like', "%{$q}%")
                        ->orWhere('middle_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('admission_no', 'like', "%{$q}%")
                        ->orWhereHas('sessions.classSection.schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('sessions.classSection.section', fn ($sectionQuery) => $sectionQuery->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $students->map(function (Student $student): array {
                $className = $this->studentCurrentClass($student);
                $admissionNo = $student->admission_no ?: 'No admission no';

                return [
                    'id' => $student->id,
                    'text' => sprintf('%s (%s) - %s', $student->full_name, $admissionNo, $className),
                    'student_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'class_name' => $className,
                ];
            }),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StudentDocument::class);

        return view('modules.documents.create', [
            'documentTypes' => StudentDocument::documentTypes(),
            'documentCategories' => StudentDocument::documentCategories(),
            'classes' => SchoolClass::query()->whereHas('sections')->with('sections')->get(),
        ]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', StudentDocument::class);

        $data = $request->validated();
        $file = $request->file('file');

        $document = $this->service->create($data, $file);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function show(StudentDocument $document)
    {
        $this->authorize('view', $document);

        $document->load(['student', 'uploader']);

        if (request()->expectsJson()) {
            $document->loadMissing(['student.sessions.classSection.schoolClass', 'student.sessions.classSection.section']);

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'student_id' => $document->student_id,
                    'student_name' => $document->student?->full_name,
                    'admission_no' => $document->student?->admission_no,
                    'student_option_label' => $document->student
                        ? sprintf(
                            '%s (%s) - %s',
                            $document->student->full_name,
                            $document->student->admission_no ?: 'No admission no',
                            $this->studentCurrentClass($document->student),
                        )
                        : null,
                    'student_class' => $document->student ? $this->studentCurrentClass($document->student) : '-',
                    'document_type' => $document->document_type,
                    'document_type_label' => $document->document_type_label,
                    'title' => $document->title,
                    'file_name' => $document->file_name,
                    'file_path' => $document->file_path,
                    'file_size' => $document->file_size ? round($document->file_size / 1024, 1) . ' KB' : '-',
                    'mime_type' => $document->mime_type,
                    'issue_date' => $document->issue_date?->format('d M Y'),
                    'expiry_date' => $document->expiry_date?->format('d M Y'),
                    'remarks' => $document->remarks,
                    'is_verified' => $document->is_verified,
                    'uploader_name' => $document->uploader?->name,
                    'created_at' => $document->created_at?->format('d M Y h:i A'),
                ],
            ]);
        }

        return view('modules.documents.show', compact('document'));
    }

    public function edit(StudentDocument $document): View
    {
        $this->authorize('update', $document);

        return view('modules.documents.edit', [
            'document' => $document,
            'documentTypes' => StudentDocument::documentTypes(),
            'documentCategories' => StudentDocument::documentCategories(),
            'classes' => SchoolClass::query()->whereHas('sections')->with('sections')->get(),
        ]);
    }

    public function update(UpdateDocumentRequest $request, StudentDocument $document): JsonResponse
    {
        $this->authorize('update', $document);

        $data = $request->validated();
        $file = $request->file('file');

        $document = $this->service->update($document->id, $data, $file);

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully.',
            'document' => $document,
        ]);
    }

    public function destroy(StudentDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function toggleVerify(StudentDocument $document): JsonResponse
    {
        $this->authorize('verify', $document);

        $document->update(['is_verified' => !$document->is_verified]);

        return response()->json([
            'success' => true,
            'message' => $document->is_verified ? 'Document verified.' : 'Verification removed.',
        ]);
    }

    public function download(StudentDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    private function studentCurrentClass(Student $student): string
    {
        $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();
        $classSection = $session?->classSection;

        if (! $classSection) {
            return 'No active class';
        }

        return trim(($classSection->schoolClass?->name ?? 'Class') . ' - ' . ($classSection->section?->name ?? 'Section'));
    }
}
