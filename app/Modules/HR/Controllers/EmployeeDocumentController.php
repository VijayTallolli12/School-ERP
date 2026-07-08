<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\HR\Requests\StoreEmployeeDocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDocumentController extends Controller
{
    public function index(): View
    {
        return view('modules.hr.documents');
    }

    public function data(): JsonResponse
    {
        return DataTables::of(EmployeeDocument::query()->with(['employee', 'verifier']))
            ->addColumn('employee_name', fn (EmployeeDocument $doc) => e($doc->employee->full_name))
            ->addColumn('status_badge', function (EmployeeDocument $doc): string {
                $class = match ($doc->status) {
                    'verified' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge bg-'.$class.'">'.e(ucfirst($doc->status)).'</span>';
            })
            ->addColumn('actions', fn (EmployeeDocument $doc) => view('modules.hr._actions_document', compact('doc'))->render())
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function store(StoreEmployeeDocumentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('hr/documents', 'public');
        }

        $document = EmployeeDocument::query()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'data' => $document,
        ]);
    }

    public function show(EmployeeDocument $document): JsonResponse
    {
        $document->load(['employee', 'verifier']);

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    public function update(StoreEmployeeDocumentRequest $request, EmployeeDocument $document): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('hr/documents', 'public');
        }

        $document->fill($data)->save();

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully.',
            'data' => $document->refresh(),
        ]);
    }

    public function destroy(EmployeeDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function verify(EmployeeDocument $document): JsonResponse
    {
        $this->authorize('verify', $document);

        $document->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document verified successfully.',
            'data' => $document->refresh(),
        ]);
    }
}
