@extends('layouts.admin')

@section('page-title')
    @yield('title')
@endsection


@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-4 text-center">
                <h1 class="h3 fw-semibold mb-0">@yield('report_title')</h1>
            </div>

            @yield('content')

            <div class="text-center mt-4 text-muted small">
                Generated on {{ date('Y-m-d H:i:s') }}
            </div>
        </div>
    </div>
@endsection

