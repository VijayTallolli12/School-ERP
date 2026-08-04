@extends('layouts.admin')

@section('title', 'Bulk Promotion')
@section('page-title', 'Bulk Student Promotion')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.lifecycle.index') }}">Student Lifecycle</a></li>
    <li class="breadcrumb-item active">Bulk Promotion</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0"><i class="ti ti-arrow-up-circle text-primary me-2"></i>Promote Students</h3>
        </div>
        <div class="card-body">
            @include('modules.lifecycle._promote')
        </div>
    </div>
@endsection
