@extends('layouts.admin')

@section('page-title')
    @yield('title')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

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

@prepend('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endprepend
