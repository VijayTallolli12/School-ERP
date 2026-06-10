<?php

namespace App\Modules\Documents\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Documents\Repositories\DocumentRepositoryInterface;
use App\Modules\Documents\Requests\StoreDocumentRequest;
use App\Modules\Documents\Requests\UpdateDocumentRequest;
use App\Modules\Documents\Services\DocumentService;
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
            ->addColumn('issue_date', fn (StudentDocument $doc) => $doc->issue_date?->format('d M Y') ?? '-')
            ->addColumn('expiry_date', fn (StudentDocument $doc) => $doc->expiry_date?->format('d M Y') ?? '-')
            ->addColumn('status', fn (StudentDocument $doc) => view('modules.documents._status', ['doc' => $doc])->render())
            ->addColumn('actions', fn (StudentDocument $doc) => view('modules.documents._actions', ['doc' => $doc])->render())
            ->filterColumn('student', fn ($query, $keyword) => $query->whereHas('student', fn ($q) => $q->where('first_name', 'like', "%{$keyword}%")->orWhere('last_name', 'like', "%{$keyword}%")))
            ->rawColumns(['student', 'document_type', 'status', 'actions'])
            ->make(true);
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
        $data['uploaded_by'] = auth()->id();

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        $document = $this->service->createDocument($data);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function show(StudentDocument $document): View
    {
        $this->authorize('view', $document);

        return view('modules.documents.show', [
            'document' => $document->load(['student', 'uploader']),
        ]);
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

        if ($request->hasFile('file_path')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        $document = $this->service->updateDocument($document, $data);

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

    public function download(StudentDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}
