@extends('layouts.admin')

@section('title', 'My Documents')
@section('page-title', 'My Documents')

@section('breadcrumbs')
    <li class="breadcrumb-item active">My Documents</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><i class="ti ti-file-text text-primary me-2"></i>Employment Documents</h3>
                </div>
                <div class="card-body">
                    @if($teacher && $documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Uploaded At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $doc)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                                            <td>{{ $doc->uploaded_at?->format('d M Y h:i A') ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.teacher-documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-file-off d-block fs-1 text-secondary mb-2 opacity-25"></i>
                            <p class="text-secondary">No employment documents uploaded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
