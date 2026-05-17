@extends("Reports::reports_layout")

@section("title", "Student List Report - Print")
@section("report_title", "Student List Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Guardian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->user->first_name . ' ' . $student->user->last_name }}</td>
                    <td>{{ $student->admission_no }}</td>
                    <td>
                        @php
                            $session = $student->sessions->first();
                            echo $session ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name : '';
                        @endphp
                    </td>
                    <td>{{ $student->guardians->pluck('user.first_name')->join(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push("scripts")
<script>
    window.print();
</script>
@endpush