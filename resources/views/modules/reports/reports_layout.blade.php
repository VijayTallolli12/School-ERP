@extends('layouts.admin')

@section('page-title')
    @yield('title')
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Analytics Hub</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('page-tabs')
    @if(request()->routeIs('reports.students.*'))
        @include('modules.reports.partials.navbar_students')
    @elseif(request()->routeIs('reports.attendance.*'))
        @include('modules.reports.partials.navbar_attendance')
    @elseif(request()->routeIs('reports.fees.*'))
        @include('modules.reports.partials.navbar_fees')
    @elseif(request()->routeIs('reports.exams.*'))
        @include('modules.reports.partials.navbar_exams')
    @elseif(request()->routeIs('reports.teachers.*'))
        @include('modules.reports.partials.navbar_teachers')
    @elseif(request()->routeIs('reports.parents.*'))
        @include('modules.reports.partials.navbar_parents')
    @endif
@endsection
