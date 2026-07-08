@extends("Reports::reports_layout")

@section("title", "Student Directory Report - Print")
@section("report_title", "Student Directory Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Gender</th>
                <th>Guardian</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->class_section }}</td>
                    <td>{{ ucfirst($student->gender) }}</td>
                    <td>{{ $student->guardian_name }}</td>
                    <td>{{ $student->guardian_phone }}</td>
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
