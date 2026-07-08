<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class TeacherDocumentController extends Controller
{
    public function index(): View
    {
        $teacher = Teacher::query()->where('user_id', auth()->id())->first();

        return view('modules.documents.teacher.index', [
            'teacher' => $teacher,
            'documents' => $teacher ? $teacher->documents()->latest()->get() : collect(),
        ]);
    }

    public function data(): JsonResponse
    {
        $teacher = Teacher::query()->where('user_id', auth()->id())->first();

        if (!$teacher) {
            return DataTables::of(collect())->toJson();
        }

        return DataTables::of($teacher->documents()->getQuery())
            ->addColumn('document_type', fn (TeacherDocument $doc) => ucfirst(str_replace('_', ' ', $doc->document_type)))
            ->addColumn('uploaded_at', fn (TeacherDocument $doc) => $doc->uploaded_at?->format('d M Y h:i A') ?? '-')
            ->addColumn('actions', fn (TeacherDocument $doc) => '
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="'.route('admin.teacher-documents.download', $doc->id).'" title="Download"><i class="ti ti-download"></i></a>
                </div>')
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function download(TeacherDocument $document): StreamedResponse
    {
        $teacher = Teacher::query()->where('user_id', auth()->id())->first();

        if (!$teacher || $document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path);
    }
}
